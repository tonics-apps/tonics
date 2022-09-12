<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;


trait ConsoleColor
{
    private array $fgColors = [];
    private array $bgColors = [];

    private string $errorMessage = '';
    ## Could be a success message or info message
    private string $otherMessage = '';
    private bool $passes = true;

    private bool $isCLI = true;

    /**
     * @throws \Exception
     */
    public function initShellColors()
    {
        $this->fgColors['black'] = '0;30';
        $this->fgColors['dark_gray'] = '1;30';
        $this->fgColors['red'] = '0;31';
        $this->fgColors['light_red'] = '1;31';
        $this->fgColors['green'] = '0;32';
        $this->fgColors['light_green'] = '1;32';
        $this->fgColors['brown'] = '0;33';
        $this->fgColors['yellow'] = '1;33';
        $this->fgColors['blue'] = '0;34';
        $this->fgColors['light_blue'] = '1;34';
        $this->fgColors['purple'] = '0;35';
        $this->fgColors['light_purple'] = '1;35';
        $this->fgColors['cyan'] = '0;36';
        $this->fgColors['light_cyan'] = '1;36';
        $this->fgColors['light_gray'] = '0;37';
        $this->fgColors['white'] = '1;37';

        $this->bgColors['black'] = '40';
        $this->bgColors['red'] = '41';
        $this->bgColors['green'] = '42';
        $this->bgColors['yellow'] = '43';
        $this->bgColors['blue'] = '44';
        $this->bgColors['magenta'] = '45';
        $this->bgColors['cyan'] = '46';
        $this->bgColors['light_gray'] = '47';

        $this->isCLI = helper()->isCLI();
    }

    /**
     * @param string $fgColor
     * @param string $bgColor
     * @param string $message
     * @return string
     * @throws \Exception
     */
    public function coloredText(string $fgColor = 'black', string $bgColor = 'yellow', string $message = ''): string
    {
        $this->initShellColors();

        if (key_exists($fgColor, $this->fgColors) && key_exists($bgColor, $this->bgColors)){
            $fgColor = $this->fgColors[$fgColor];
            $bgColor = $this->bgColors[$bgColor];
            $message ="\e[{$fgColor};{$bgColor}m{$message}\e[0m\n";
        }

        return $message;
    }

    /**
     * @param $message
     * @throws \Exception
     */
    public function successMessage($message)
    {
        $this->otherMessage = $message;
        $this->passes = true;
        if ($this->isCLI){
            echo $this->coloredText("black","green", "$message ✔");
        }
    }

    /**
     * @param $message
     * @throws \Exception
     */
    public function errorMessage($message)
    {
        $this->errorMessage = $message;
        $this->passes = false;
        if ($this->isCLI){
            echo $this->coloredText("white","red", "$message ❌");
        }
    }

    /**
     * @param $message
     * @throws \Exception
     */
    public function infoMessage($message): void
    {
        $this->otherMessage = $message;
        $this->passes = true;
        if ($this->isCLI){
            echo $this->coloredText("black", "light_gray", "$message !");
        }
    }

    /**
     * @param $message
     * @throws \Exception
     */
    public function delayMessage($message)
    {
        $this->otherMessage = $message;
        $this->passes = true;
        if ($this->isCLI){
            echo $this->coloredText("black", "yellow", "$message...");
        }
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Could be a success message or info message
     * @return string
     */
    public function getOtherMessage(): string
    {
        return $this->otherMessage;
    }

    /**
     * @return bool
     */
    public function passes(): bool
    {
        return $this->passes;
    }
}