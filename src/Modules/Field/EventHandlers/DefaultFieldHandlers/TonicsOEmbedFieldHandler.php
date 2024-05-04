<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Field\EventHandlers\DefaultFieldHandlers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Embera\Embera;
use Embera\Provider\ProviderAdapter;
use Embera\Provider\ProviderInterface;
use Embera\ProviderCollection\DefaultProviderCollection;
use Embera\Url;

class TonicsOEmbedFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @param OnFieldMetaBox|null $event
     * @param $fields
     * @return string
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        $embedFrag = '';
        $url = '';
        $responsive = true;
        $width = 0;
        $height = 0;
        if (isset($fields[0]->_children)) {
            $OEmbed_url = 'OEmbed_url';
            $OEmbed_width = 'OEmbed_width';
            $OEmbed_height = 'OEmbed_height';
            $OEmbed_responsive = 'OEmbed_responsive';

            foreach ($fields[0]->_children as $child) {
                if ($child->field_input_name === $OEmbed_url) {
                    $url = $child->field_data[$OEmbed_url] ?? '';
                }

                if ($child->field_input_name === $OEmbed_width) {
                    $responsive = $child->field_data[$OEmbed_width] ?? '';
                    if ($responsive === '0'){
                        $responsive = false;
                    }
                }

                if ($child->field_input_name === $OEmbed_height) {
                    $width = $child->field_data[$OEmbed_height] ?? '';
                }

                if ($child->field_input_name === $OEmbed_responsive) {
                    $height = $child->field_data[$OEmbed_responsive] ?? '';
                }
            }

            $config = [
                'https_only' => true,
                'width' => (int)$width,
                'height' => (int)$height,
                'responsive' => $responsive,
                'fake_responses' => Embera::DISABLE_FAKE_RESPONSES,
            ];
            $host = parse_url(AppConfig::getAppUrl(), PHP_URL_HOST);
            $collection = new DefaultProviderCollection();
            if (!empty($host)){
                $collection->addProvider("$host", TonicsOEmbedProvider::class);
            }

            $ember = new Embera($config, $collection);
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // Never Use the autoEmbed, can cause xss
            $embedFrag = $ember->getUrlData($url);
            $embedFrag = $embedFrag[$url]['html'] ?? '';
        }

        return $embedFrag;
    }

    public function name(): string
    {
        return 'OEmbed';
    }

    public function fieldSlug(): string
    {
       return 'oembed';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }
}

class TonicsOEmbedProvider extends ProviderAdapter implements ProviderInterface
{
    /** inline {@inheritdoc} */
    protected $httpsSupport = true;

    /** inline {@inheritdoc} */
    protected $responsiveSupport = true;

    public function validateUrl(Url $url): bool
    {
        $urlPaths = parse_url((string) $url, PHP_URL_PATH);
        if (str_starts_with($urlPaths, '/posts')){
            return true;
        }
        // we can check other paths, e.g /tracks, /genres, we omit it for now...
        return false;
    }

    public function getEndpoint(): string
    {
        return AppConfig::getAppUrl() . '/services/oembed?format=json';
    }

    public function modifyResponse(array $response = []): array
    {
        return $response;
    }

    public function getFakeResponse(): array
    {
        return [];
    }

    public function normalizeUrl(Url $url): Url
    {
        $url->convertToHttps();
        $url->removeQueryString();
        return $url;
    }

    public static function getHosts(): array
    {
        return
            [
                parse_url(AppConfig::getAppUrl(), PHP_URL_HOST)
            ];
    }
}