<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\EventHandlers\Messages\Logs\TonicsErr;
use App\Modules\Core\EventHandlers\Messages\Logs\TonicsLog;
use App\Modules\Core\EventHandlers\Messages\Logs\TonicsNginxAccess;
use App\Modules\Core\EventHandlers\Messages\Logs\TonicsNginxError;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

class LogController
{
    const TonicsModule_TonicsCoreLogControllerSettings = 'TonicsModule_TonicsCoreLogControllerSettings';
    private static array $settings = [];

    /**
     * @param FieldData $fieldData
     */
    public function __construct (private readonly FieldData $fieldData) {}

    /**
     * @throws \Throwable
     */
    public function view (): void
    {
        $settings = self::getSettingsData();
        $logPath = $settings['log_path'] ?? self::WhiteListLogFiles()[0];
        if (isset(self::WhiteListLogFiles()[$logPath])) {

            $options = [
                'n'        => $settings['log_lines'] ?? 500,
                'skip'     => 0,
                'trim'     => true,
                'newline'  => "\n",
                'output'   => 'string',
                'log_path' => $logPath, // not required, just for saving
            ];

            try {
                $lines = helper()->getLastLines($logPath, $options);
            } catch (\Exception $exception) {
                session()->flash([$exception->getMessage()], []);
                $lines = '';
            }
            FieldConfig::savePluginFieldSettings(self::getCacheKey(), $options);
        }

        $messageURL = null;
        if (isset(self::WhiteListLogFiles()[$logPath])) {
            $messageURL = route('messageEvent', [self::WhiteListLogFiles()[$logPath] ?? null]);
        }

        view('Modules::Core/Views/Log/index', [
            'Lines'      => $this::ansiEscapesToHtml($lines ?? ''),
            'SiteURL'    => AppConfig::getAppUrl(),
            'messageURL' => $messageURL,
            'FieldItems' => $this->fieldData
                ->generateFieldWithFieldSlug(['log-page'], $settings)->getHTMLFrag(),
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function update (): void
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);

            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tools.log'));
        } catch (\Exception) {
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('admin.core.settings'));
        }
    }

    /**
     * @param string $key
     *
     * @return array|false|mixed
     * @throws \Exception
     */
    public static function getSettingsData (): mixed
    {
        if (!self::$settings) {
            $settings = apcu_fetch(self::getCacheKey());
            if ($settings === false) {
                $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
            }
            self::$settings = $settings;
        }

        return self::$settings;
    }

    public static function getCacheKey (): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsModule_TonicsCoreLogControllerSettings;
    }

    /**
     * A rewrite of this class into a single method:
     * https://github.com/neilime/ansi-escapes-to-html/blob/main/src/AnsiEscapesToHtml/Highlighter.php
     *
     * @param string $subject
     *
     * @return string
     */
    public static function ansiEscapesToHtml (string $subject): string
    {
        $conversionTags = [
            'CONTAINER_TAG'   => 'span',
            'END_OF_LINE_TAG' => '<hr>',
        ];

        $definedStyles = [
            0   => 'font-weight:normal;text-decoration:none;color:white;background-color:black',
            1   => 'font-weight:bold',
            2   => '',
            4   => 'text-decoration:underline',
            5   => '',
            7   => '',
            8   => 'display:none',
            21  => 'font-weight:normal',
            22  => '',
            24  => 'text-decoration:none',
            25  => 'text-decoration:none',
            27  => '',
            28  => 'display:inline-block',
            39  => 'color:white',
            30  => 'color:black',
            31  => 'color:red',
            32  => 'color:green',
            33  => 'color:yellow',
            34  => 'color:blue',
            35  => 'color:magenta',
            36  => 'color:cyan',
            37  => 'color:lightgray',
            90  => 'color:darkgray',
            91  => 'color:lightRed',
            92  => 'color:lightgreen',
            93  => 'color:lightyellow',
            94  => 'color:lightblue',
            95  => 'color:#F466CC',
            96  => 'color:lightcyan',
            97  => 'color:white',
            49  => 'background-color:black',
            40  => 'background-color:black',
            41  => 'background-color:red',
            42  => 'background-color:green',
            43  => 'background-color:yellow',
            44  => 'background-color:blue',
            45  => 'background-color:magenta',
            46  => 'background-color:cyan',
            47  => 'background-color:lightgray',
            100 => 'background-color:darkgray',
            101 => 'background-color:lightred',
            102 => 'background-color:lightgreen',
            103 => 'background-color:lightyellow',
            104 => 'background-color:lightblue',
            105 => 'background-color:#F466CC',
            106 => 'background-color:lightcyan',
            107 => 'background-color:white',
        ];

        $rgbColors = [];

        $getRgbColor = function (int $colorNumber) use (&$rgbColors): string {
            if (!$rgbColors) {
                for ($red = 0; $red < 6; $red++) {
                    for ($green = 0; $green < 6; $green++) {
                        for ($blue = 0; $blue < 6; $blue++) {
                            $keyColorNumber = 16 + ($red * 36) + ($green * 6) + $blue;
                            $rgb = sprintf('rgb(%d,%d,%d)', ($red ? $red * 40 + 55 : 0), ($green ? $green * 40 + 55 : 0), ($blue ? $blue * 40 + 55 : 0));
                            $rgbColors[$keyColorNumber] = $rgb;
                        }
                    }
                }
                for ($gray = 0; $gray < 24; $gray++) {
                    $keyColorNumber = $gray + 232;
                    $grayValue = $gray * 10 + 8;
                    $rgbColors[$keyColorNumber] = "rgb($grayValue,$grayValue,$grayValue)";
                }
            }

            if (isset($rgbColors[$colorNumber])) {
                return $rgbColors[$colorNumber];
            }

            throw new \InvalidArgumentException(sprintf('Argument "$colorNumber" "%d" expects to be in range 16,256', $colorNumber));
        };

        $getCssStylesFromCode = function (string $code, array $styles) use ($definedStyles, $getRgbColor): array {
            $code = trim($code);
            if (preg_match('/(38|48);5;([0-9]+)/', $code, $matches)) {
                $colorNumber = (int)$matches[2];
                $isForegroundColor = (int)$matches[1] === 38;
                if ($isForegroundColor) {
                    $styles['color'] = $getRgbColor($colorNumber);
                } else {
                    $styles['background-color'] = $getRgbColor($colorNumber);
                }
                $code = trim(str_replace($matches[0], '', $code));
            }
            if ($code !== '') {
                foreach (explode(';', $code) as $codePart) {
                    $codeNumber = (int)trim($codePart);
                    if (isset($definedStyles[$codeNumber])) {
                        foreach (explode(';', $definedStyles[$codeNumber]) as $style) {
                            [$key, $value] = explode(':', $style);
                            $styles[$key] = $value;
                        }
                    }
                }
            }
            return $styles;
        };

        $openContainerWithStyles = function (array $styles) use ($conversionTags): string {
            $stylesContent = '';
            foreach ($styles as $style => $value) {
                if ($value) {
                    $stylesContent .= $style . ':' . $value . ';';
                }
            }
            return '<' . $conversionTags['CONTAINER_TAG'] . ' style="' . $stylesContent . '">';
        };

        $closeContainer = function () use ($conversionTags): string {
            return '</' . $conversionTags['CONTAINER_TAG'] . '>';
        };

        $styles = [];
        foreach (explode(';', $definedStyles[0]) as $style) {
            [$key, $value] = explode(':', $style);
            $styles[$key] = $value;
        }

        $lines = explode(PHP_EOL, $subject);
        $convertedLines = [];

        foreach ($lines as $line) {
            $isContainerOpen = false;
            if (!preg_match('/^(\e\[([0-9;]+)m)/', $line)) {
                $line = $openContainerWithStyles($styles) . $line;
                $isContainerOpen = true;
            }

            $line = preg_replace_callback(
                '/(\e\[([0-9;]+)m)/',
                function ($matches) use ($getCssStylesFromCode, &$styles, &$isContainerOpen, $openContainerWithStyles, $closeContainer) {
                    $result = '';
                    if ($isContainerOpen) {
                        $result .= $closeContainer();
                        $isContainerOpen = false;
                    }
                    $styles = $getCssStylesFromCode($matches[2], $styles);
                    $result .= $openContainerWithStyles($styles);
                    $isContainerOpen = true;
                    return $result;
                },
                $line,
            );

            if ($isContainerOpen) {
                $line .= $closeContainer();
            }

            $convertedLines[] = "<span> $line </span>";
        }

        return implode($conversionTags['END_OF_LINE_TAG'], $convertedLines);
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    public static function WhiteListLogFiles (): array
    {
        return [
            TonicsLog::FilePath()         => TonicsLog::MessageTypeKey(),
            TonicsErr::FilePath()         => TonicsErr::MessageTypeKey(),
            TonicsNginxAccess::FilePath() => TonicsNginxAccess::MessageTypeKey(),
            TonicsNginxError::FilePath()  => TonicsNginxError::MessageTypeKey(),
        ];
    }
}