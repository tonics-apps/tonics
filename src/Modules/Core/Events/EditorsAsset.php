<?php

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