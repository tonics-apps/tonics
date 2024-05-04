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

namespace App\Apps\TonicsAI\Library\OpenAI;

/**
 * Credits To: https://github.com/orhanerday/open-ai
 */
class Url
{
    public const ORIGIN = 'https://api.openai.com';
    public const API_VERSION = 'v1';
    public const OPEN_AI_URL = self::ORIGIN . "/" . self::API_VERSION;

    /**
     * @param string $engine
     * @return string
     * @deprecated
     */
    public static function completionURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/completions";
    }

    /**
     * @return string
     */
    public static function completionsURL(): string
    {
        return self::OPEN_AI_URL . "/completions";
    }

    /**
     *
     * @return string
     */
    public static function editsUrl(): string
    {
        return self::OPEN_AI_URL . "/edits";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function searchURL(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine/search";
    }

    /**
     * @param
     * @return string
     */
    public static function enginesUrl(): string
    {
        return self::OPEN_AI_URL . "/engines";
    }

    /**
     * @param string $engine
     * @return string
     */
    public static function engineUrl(string $engine): string
    {
        return self::OPEN_AI_URL . "/engines/$engine";
    }

    /**
     * @param
     * @return string
     */
    public static function classificationsUrl(): string
    {
        return self::OPEN_AI_URL . "/classifications";
    }

    /**
     * @param
     * @return string
     */
    public static function moderationUrl(): string
    {
        return self::OPEN_AI_URL . "/moderations";
    }

    /**
     * @param
     * @return string
     */
    public static function transcriptionsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/transcriptions";
    }

    /**
     * @param
     * @return string
     */
    public static function translationsUrl(): string
    {
        return self::OPEN_AI_URL . "/audio/translations";
    }

    /**
     * @param
     * @return string
     */
    public static function filesUrl(): string
    {
        return self::OPEN_AI_URL . "/files";
    }

    /**
     * @param
     * @return string
     */
    public static function fineTuneUrl(): string
    {
        return self::OPEN_AI_URL . "/fine-tunes";
    }

    /**
     * @param
     * @return string
     */
    public static function fineTuneModel(): string
    {
        return self::OPEN_AI_URL . "/models";
    }

    /**
     * @param
     * @return string
     */
    public static function answersUrl(): string
    {
        return self::OPEN_AI_URL . "/answers";
    }

    /**
     * @param
     * @return string
     */
    public static function imageUrl(): string
    {
        return self::OPEN_AI_URL . "/images";
    }

    /**
     * @param
     * @return string
     */
    public static function embeddings(): string
    {
        return self::OPEN_AI_URL . "/embeddings";
    }

    /**
     * @param
     * @return string
     */
    public static function chatUrl(): string
    {
        return self::OPEN_AI_URL . "/chat/completions";
    }
}
