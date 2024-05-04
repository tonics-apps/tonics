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

namespace App\Apps\TonicsSeo\EventHandler;

use App\Modules\Field\Events\OnEditorFieldSelection;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsStructuredDataFAQHandlerAndSelection implements FieldTemplateFileInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnEditorFieldSelection */
        $event->addField('FAQ', 'app-tonicsseo-structured-data-faq', category: OnEditorFieldSelection::CATEGORY_StructuredData);
    }

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        $faqPlain = '';
        $structuredData = isset(getGlobalVariableData()['Structured_Data']) ? getGlobalVariableData()['Structured_Data'] : null;
        if (key_exists('FAQ', $structuredData)) {
            foreach ($fields as $field) {
                $question = (isset($field->_children[0]->field_data['app_tonics_seo_structured_data_faq_question']))
                    ? helper()->htmlSpecChar($field->_children[0]->field_data['app_tonics_seo_structured_data_faq_question'])
                    : null;

                $answer = null;
                if (isset($field->_children[1]->field_data['app_tonics_seo_structured_data_faq_answer'])){
                    $answer = nl2br($field->_children[1]->field_data['app_tonics_seo_structured_data_faq_answer']);
                    $answer = helper()->htmlSpecChar($answer);
                    $answer  = str_replace(['&lt;br /&gt;'], ['<br />'], $answer);
                }

                $faqPlain .=<<<FAQPLAIN
<h2 class="tonics-structured-data-faq-question">$question</h2>
<p class="tonics-structured-data-faq-answer">$answer</p>
FAQPLAIN;


                $structuredData['FAQ'][] = <<<FAQ_SCHEMA
{
        "@type": "Question",
        "name": "$question",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "$answer"
        }
      }
FAQ_SCHEMA;
            }
        }

        addToGlobalVariable('Structured_Data', $structuredData);
        return '<div class="tonics-structured-data-faq owl">' . $faqPlain . '</div>';
    }

    public function name(): string
    {
        return 'Tonics Structured Data FAQ';
    }

    public function fieldSlug(): string
    {
        return 'app-tonicsseo-structured-data-faq';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return false;
    }

    /**
     * @param array $appTonicsseoStructuredDataFaqSchemaData
     * @return string
     */
    public static function handleStructuredData(array $appTonicsseoStructuredDataFaqSchemaData): string
    {
        $faqSchemaFrag = '';
        if (!empty($appTonicsseoStructuredDataFaqSchemaData)){
            $faqSchemaFrag = <<<SchemaFAQ
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
SchemaFAQ;
            $lastKey = array_key_last($appTonicsseoStructuredDataFaqSchemaData);
            foreach ($appTonicsseoStructuredDataFaqSchemaData as $key => $faqSchema){
                if ($lastKey === $key){
                    $faqSchemaFrag .= $faqSchema;
                } else {
                    $faqSchemaFrag .= $faqSchema . ',';
                }
            }

            $faqSchemaFrag .= <<<ShemaFAQ
]
    }
</script>

ShemaFAQ;
        }

        return $faqSchemaFrag;
    }
}