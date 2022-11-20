<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
        $structuredData = isset(getGlobalVariableData()['Structured_Data']) ? getGlobalVariableData()['Structured_Data'] : null;
        if (key_exists('FAQ', $structuredData)) {
            foreach ($fields as $field) {
                $question = (isset($field->_children[0]->field_data['app_tonics_seo_structured_data_faq_question']))
                    ? helper()->htmlSpecChar($field->_children[0]->field_data['app_tonics_seo_structured_data_faq_question'])
                    : null;

                $answer = (isset($field->_children[1]->field_data['app_tonics_seo_structured_data_faq_answer']))
                    ? helper()->htmlSpecChar($field->_children[1]->field_data['app_tonics_seo_structured_data_faq_answer'])
                    : null;

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
        return '';
        // return 'FAQ Placeholder';
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