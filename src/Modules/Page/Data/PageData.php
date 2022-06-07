<?php

namespace App\Modules\Page\Data;

use App\Library\AbstractDataLayer;
use App\Library\CustomClasses\UniqueSlug;
use App\Library\Tables;

class PageData extends AbstractDataLayer
{
    use UniqueSlug;
    
    public function getPageTable(): string
    {
        return Tables::getTable(Tables::PAGES);
    }

    public function getPageColumns(): array
    {
        return [ 'page_id', 'field_ids', 'page_title', 'page_slug', 'page_status', 'field_settings', 'created_at', 'updated_at'];
    }

    /**
     * @param $pages
     * @param int|null $status
     * @return string
     * @throws \Exception
     */
    public function adminPageListing($pages, int|null $status = 1): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = ''; $urlPrefix = "/admin/pages";
        foreach ($pages as $k => $page) {
            if ($page->page_status === $status || $status === null){
                if ($page->page_status === -1){
                    $otherFrag = <<<HTML
<form method="post" class="d:contents" action="$urlPrefix/$page->page_id/delete">
   <input type="hidden" name="token" value="$csrfToken">
       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Delete
        </button>
</form>
HTML;
                } else {
                    $otherFrag = <<<HTML
<form method="post" class="d:contents" action="$urlPrefix/$page->page_id/trash">
   <input type="hidden" name="token" value="$csrfToken" >
       <button data-click-onconfirmtrash="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Trash
        </button>
</form>
HTML;
                }

                $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k" data-id="$page->page_id"  
    data-page_id="$page->page_id" 
    data-page_slug="$page->page_slug" 
    data-page_title="$page->page_title"
    data-db_click_link="$urlPrefix/$page->page_id/edit"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$page->page_title</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$page->page_title</div>
         
                <div class="form-group d:flex flex-gap:small">
                     <a href="$urlPrefix/$page->page_id/edit" class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer button:box-shadow-variant-2">Edit</a>
                         $otherFrag
                </div>
            </div>
        </fieldset>
    </li>
HTML;
            }
        }

        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function createPage(array $ignore = []): array
    {
        $slug = $this->generateUniqueSlug($this->getPageTable(),
            'page_slug', helper()->slug(input()->fromPost()->retrieve('page_slug')));

        $_POST['field_settings'] = input()->fromPost()->all();
        unset($_POST['field_settings']['token']);

        $page = []; $postColumns = array_flip($this->getPageColumns());
        foreach (input()->fromPost()->all() as $inputKey => $inputValue){
            if (key_exists($inputKey, $postColumns) && input()->fromPost()->has($inputKey)){

                if($inputKey === 'created_at'){
                    $page[$inputKey] = helper()->date(timestamp: $inputValue);
                    continue;
                }
                if ($inputKey === 'page_slug'){
                    $page[$inputKey] = $slug;
                    continue;
                }
                $page[$inputKey] = $inputValue;
            }
        }

        $ignores = array_diff_key($ignore, $page);
        if (!empty($ignores)){
            foreach ($ignores as $v){
                unset($page[$v]);
            }
        }

        if (isset($page['field_ids'])){
            $page['field_ids'] = array_values(array_flip(array_flip($page['field_ids'])));
            $page['field_ids'] = json_encode($page['field_ids']);
        }

        if (isset($page['field_settings'])){
            $page['field_settings'] = json_encode($page['field_settings']);
            if (isset($page['field_ids'])){
                $_POST['field_settings']['field_ids'] = $page['field_ids'];
            }
        }

        return $page;
    }
}