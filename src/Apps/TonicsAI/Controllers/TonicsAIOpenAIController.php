<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Apps\TonicsAI\Controllers;

use App\Apps\TonicsAI\Library\OpenAI\OpenAi;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Media\FileManager\LocalDriver;

class TonicsAIOpenAIController
{

    /**
     * @throws \Exception
     */
    public function chat()
    {
        $requestBody = json_decode(request()->getEntityBody());
        if (!isset($requestBody->messages)){
            response()->onError(400, 'Request Body Malformed');
        }

        if (!is_array($requestBody->messages)){
            response()->onError(400, 'Request Body Malformed');
        }

        $messages = [];
        foreach ($requestBody->messages as $message){
            $messages[] = [
                "role" => $message->role,
                "content" => $message->content,
            ];
        }

        helper()->addEventStreamHeader();
        $settingsData = TonicsAISettingsController::getSettingData();
        $open_ai = new OpenAi($settingsData[TonicsAISettingsController::Key_OpenAIKey]);
        $open_ai->chat([
            'model' => $settingsData[TonicsAISettingsController::Key_OpenAIChatModelName],
            'messages' => $messages,
            'temperature' => 0.5,
            'max_tokens' => 2000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stream' => true
        ], function ($curl_info, $data){
            if (($obj = json_decode($data)) && isset($obj->error->message)) {
                helper()->sendMsg('OpenAIChatCompletion_Error', $obj->error->message, 'issue');
            } else {
                # Split the data by newline characters
                $chunks = explode("\n", $data);
                # Loop through the chunks and send each one as a separate message
                foreach ($chunks as $chunk) {
                    if (!empty($chunk)) {
                        helper()->sendMsg('OpenAIChatCompletion', trim($chunk));
                    }
                }
            }
            return strlen($data);
        });


    }

    /**
     * @throws \Exception
     */
    public function image()
    {
        $frag = '';
        try {
            $requestBody = json_decode(request()->getEntityBody());
            if (!isset($requestBody->prompt)){
                response()->onError(400, 'Request Body Malformed');
            }

            $requestBodySize = '512';
            if (isset($requestBody->size)){
                $requestBodySize = $requestBody->size;
            }

            $numberOfImagesToGenerate = 1;
            if (isset($requestBody->numberOfImagesToGenerate)){
                $numberOfImagesToGenerate = (int)$requestBody->numberOfImagesToGenerate;
                if ($numberOfImagesToGenerate > 10){
                    $numberOfImagesToGenerate = 1;
                }
            }

            $size = [
                '256' => '256x256',
                '512' => '512x512',
                '1024' => '1024x1024 ',
            ];

            $selectedSize = (isset($size[$requestBodySize])) ? $size[$requestBodySize] : $size['512'];

            $localDriver = new LocalDriver();
            $uploadsPath = DriveConfig::getUploadsPath();
            $currentYear = date('Y');
            $currentMonth = date('m');
            $uploadsPathWithDate = $uploadsPath . "/$currentYear/$currentMonth";
            $dirCreated = helper()->createDirectoryRecursive($uploadsPathWithDate);
            if ($dirCreated === false){
                response()->onError(400, "Error Creating Directory");
            }

            $settingsData = TonicsAISettingsController::getSettingData();
            $open_ai = new OpenAi($settingsData[TonicsAISettingsController::Key_OpenAIKey]);
            $result = $open_ai->image([
                "prompt" => $requestBody->prompt,
                "n" => $numberOfImagesToGenerate,
                "size" => $selectedSize,
            ]);
            $filesCreated = [];
            if (helper()->isJSON($result)){
                $result = json_decode($result);
                $urls = (isset($result->data) && is_array($result->data)) ? $result->data : [];
                foreach ($urls as $url){
                    $rand = helper()->randomString(5);
                    $name = $requestBody->prompt;
                    $name = $localDriver->normalizeFileName($name, '_', 200) . "_$rand.png";
                    helper()->updateActivateEventStreamMessage();
                    if ($localDriver->createFromURL($url->url, $uploadsPathWithDate, $name, importToDB: false)){
                        $localDriver->recursivelyCreateFileOrDirectory($uploadsPathWithDate . "/$name", function ($fileData) use (&$filesCreated){
                            if (isset($fileData->properties)){
                                $filesCreated[] = $fileData;
                            }
                        });
                    }
                    helper()->updateActivateEventStreamMessage(1);
                }
            } else {
                response()->onError(400, "An Error Occurred Generating Image(s), Try Again");
            }

            $frag = <<<FRAG
<div class="width:100% d:flex flex-gap:small flex-wrap:wrap position:relative">
FRAG;
            foreach ($filesCreated as $fileCreate){
                $urlPreview = DriveConfig::serveFilePath() . "$fileCreate->drive_unique_id?render";
                $frag .= <<<FRAG
<img src="$urlPreview" 
alt="$requestBody->prompt" width="$requestBodySize" height="$requestBodySize" loading="lazy" decoding="async" 
data-mce-src="$urlPreview">
FRAG;
            }
            $frag .= "</div>";
        } catch (\Exception $exception){
            helper()->updateActivateEventStreamMessage(1);
            response()->onError(400, $exception->getMessage());
            // Log...
        }

        response()->onSuccess($frag);
    }
}