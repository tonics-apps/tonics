<?php

namespace App\Modules\Field\EventHandlers\FieldSelection;

use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultFieldSelection implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnEditorFieldSelection */
        $event->addField('Download Button', 'test-field-one')
            ->addField('Test Field Two', 'test-field-two');
    }
}