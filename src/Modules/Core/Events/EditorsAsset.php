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

namespace App\Modules\Core\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class EditorsAsset implements EventInterface
{

    private array $css = [];
    private array $js = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    public function addCSS(string $cssPath): static
    {
        $this->css[] = $cssPath;
        return $this;
    }

    public function addJS(string $jsPath): static
    {
        $this->js[] = $jsPath;
        return $this;
    }

    /**
     * @return array
     */
    public function getCss(): array
    {
        return $this->css;
    }

    /**
     * @param array $css
     */
    public function setCss(array $css): void
    {
        $this->css = $css;
    }

    /**
     * @return array
     */
    public function getJs(): array
    {
        return $this->js;
    }

    /**
     * @param array $js
     */
    public function setJs(array $js): void
    {
        $this->js = $js;
    }

    public function getEditorsAssets(): string
    {
        $assetsFrag = '<template class="tiny-mce-assets">';
        foreach ($this->css as $css){
            $assetsFrag .= "<input class='css' value='$css'>";
        }
        foreach ($this->js as $js){
            $assetsFrag .= "<input class='js' value='$js'>";
        }
        $assetsFrag .='</template>';

        return $assetsFrag;
    }
}