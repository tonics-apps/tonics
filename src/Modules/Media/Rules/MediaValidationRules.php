<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Media\Rules;


use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

trait MediaValidationRules
{

    #[Pure] public function mediaRenameRules(): array
    {
        return array_merge($this->baseRule(), [
            'filename_new' => ['required', 'string']
        ]);
    }

    #[ArrayShape(['filename' => "string[]", 'uploadTo' => "string[]", 'uploadToID' => "string[]"])] public function mediaFolderCreateRule(): array
    {
        return [
            'filename' => ['required', 'string'],
            'uploadTo' => ['required', 'string'],
            'uploadToID' => ['required', 'numeric']
        ];
    }

    #[ArrayShape(['filename' => "string[]", 'uploadTo' => "string[]", 'totalBlobSize' => "string[]", 'totalChunks' => "string[]"])] #[Pure] public function mediaFileCancelUploadRule(): array
    {
        return [
            'filename' => ['required', 'string'],
            'uploadTo' => ['required', 'string'],
            'totalBlobSize' => ['required', 'numeric'],
            'totalChunks' => ['required', 'numeric'],
        ];
    }

    #[ArrayShape(['path' => "string[]", 'id' => "string[]", 'query' => "string[]", 'per_page' => "string[]"])] public function mediaFileSearchRule(): array
    {
        return [
            'path' => ['required', 'string'],
            'id' => ['required', 'numeric'],
            'query' => ['required', 'string'],
        ];
    }


    #[Pure] public function mediaFileCreateRule(): array
    {
        return [
            'filename' => ['required', 'string'],
            'chunkPart' => ['required', 'numeric'],
            'uploadTo' => ['required', 'string'],
            'uploadToID' => ['required', 'numeric'],
            'chunkSize' => ['required', 'numeric'],
            'mbRate' => ['required', 'numeric'],
            'totalBlobSize' => ['required', 'numeric'],
            'totalChunks' => ['required', 'numeric'],
            'newFile' => ['required', 'bool'],
        ];
    }


    #[Pure] public function mediaFileDeleteRule(): array
    {
        return [
            'drive_id' => ['required', 'numeric'],
            'filename' => ['required', 'string'],
            'file_path' => ['required', 'string'],
        ];
    }

    #[Pure] public function mediaFileMoveRule(): array
    {
        return [
            'drive_id' => ['required', 'numeric'],
            'filename' => ['required', 'string'],
            'file_path' => ['required', 'string'],
        ];
    }

    #[Pure] public function mediaFileUpdateRule(): array
    {
        return array_merge($this->baseRule(), [
            'chunkPart' => ['required', 'numeric'],
            'blobSize' => ['required', 'numeric'],
            'totalChunks' => ['required', 'numeric'],
        ]);
    }

    private function baseRule(): array
    {
        return [
            'drive_id' => ['required', 'numeric'],
            'filename' => ['required', 'string'],
            'file_type' => ['required', 'string'],
            'file_path' => ['required', 'string'],
            'size' => ['required', 'numeric'],
            'time_created' => ['required', 'numeric'],
            'time_modified' => ['required', 'numeric']
        ];
    }

}