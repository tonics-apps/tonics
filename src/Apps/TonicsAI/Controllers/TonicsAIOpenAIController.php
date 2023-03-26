<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAI\Controllers;

use App\Apps\TonicsAI\Library\OpenAI\OpenAi;

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
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stream' => true
        ], function ($curl_info, $data) {
            helper()->sendMsg('OpenAIChatCompletion', $data);
            return strlen($data);
        });
    }
}