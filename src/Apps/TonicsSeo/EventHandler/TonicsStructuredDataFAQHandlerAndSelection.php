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

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnEditorFieldSelection;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsStructuredDataFAQHandlerAndSelection implements FieldTemplateFileInterface, HandlerInterface
{

    public function handleEvent (object $event): void
    {
        $event->addField('FAQ', 'app-tonicsseo-structured-data-faq', category: OnEditorFieldSelection::CATEGORY_StructuredData);
    }

    /**
     * @param OnFieldMetaBox|null $event
     * @param $fields
     *
     * @return string
     * @throws \Throwable
     */
    public function handleFieldLogic (OnFieldMetaBox $event = null, $fields = null): string
    {
        $dropper = FieldConfig::getFieldSelectionDropper();
        $dropper->processLogic($fields);
        $page = $dropper->getPage();
        $htmlFrag = '';
        if (!empty($page->TonicsFAQS) && is_iterable($page->TonicsFAQS)) {
            foreach ($page->TonicsFAQS as $faq) {
                if (!empty($faq['StructuredFAQToHTML'])) {
                    $htmlFrag .= $faq['StructuredFAQToHTML'];
                }
            }
        }

        return '<div class="tonics-structured-data-faq owl">' . $htmlFrag . '</div>';
    }

    public function name (): string
    {
        return 'Tonics Structured Data FAQ';
    }

    public function fieldSlug (): string
    {
        return 'app-tonicsseo-structured-data-faq';
    }

    public function canPreSaveFieldLogic (): bool
    {
        return false;
    }

    /**
     * @param string $answer
     *
     * @return string
     * @throws \Exception
     */
    public static function cleanAnswer (string $answer): string
    {
        $answer = nl2br($answer);
        $answer = helper()->htmlSpecChar($answer);
        return str_replace(['&lt;br /&gt;'], ['<br />'], $answer);
    }

    /**
     * @param string $question
     * @param string $answer
     *
     * @return string
     * @throws \Exception
     */
    public static function StructuredFAQToHTML (string $question, string $answer): string
    {
        $question = helper()->htmlSpecChar($question);
        $answer = static::cleanAnswer($answer);
        return <<<FAQPLAIN
<h2 class="tonics-structured-data-faq-question">$question</h2>
<p class="tonics-structured-data-faq-answer">$answer</p>
FAQPLAIN;
    }

    /**
     * @param string $question
     * @param string $answer
     *
     * @return string
     * @throws \Exception
     */
    public static function StructuredFAQ (string $question, string $answer): string
    {
        $question = helper()->htmlSpecChar($question);
        $answer = static::cleanAnswer($answer);
        $structuredData = [
            "@type"          => "Question",
            "name"           => "$question",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text"  => "$answer",
            ],
        ];
        return json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * @param array $appTonicsseoStructuredDataFaqSchemaData
     *
     * @return string
     */
    public static function HandleStructuredData (array $appTonicsseoStructuredDataFaqSchemaData): string
    {
        $faqSchemaFrag = '';

        if (!empty($appTonicsseoStructuredDataFaqSchemaData)) {
            # Prepare the main structure of the JSON-LD schema
            $structuredData = [
                "@context"   => "https://schema.org",
                "@type"      => "FAQPage",
                "mainEntity" => [],
            ];
            # Loop through the provided data and add each FAQ schema to the mainEntity array
            foreach ($appTonicsseoStructuredDataFaqSchemaData as $faqSchema) {
                $structuredData['mainEntity'][] = is_string($faqSchema) ? json_decode($faqSchema) : $faqSchema;
            }
            # Convert the structured data array to JSON-LD format
            $structuredDataJson = json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            # Embed the JSON-LD script tag in the output
            $faqSchemaFrag = '<script type="application/ld+json">' . $structuredDataJson . '</script>';
        }

        return $faqSchemaFrag;
    }
}