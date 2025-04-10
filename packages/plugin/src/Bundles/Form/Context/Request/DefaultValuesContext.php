<?php

namespace Solspace\Freeform\Bundles\Form\Context\Request;

use Solspace\Freeform\Events\FormEventInterface;
use Solspace\Freeform\Library\Composer\Components\Fields\Interfaces\PersistentValueInterface;
use Solspace\Freeform\Library\Composer\Components\Form;
use yii\base\Event;

class DefaultValuesContext
{
    public function __construct()
    {
        Event::on(Form::class, Form::EVENT_REGISTER_CONTEXT, [$this, 'handleDefaultValues']);
        Event::on(Form::class, Form::EVENT_BEFORE_HANDLE_REQUEST, [$this, 'handleDefaultValues']);
    }

    public function handleDefaultValues(FormEventInterface $event)
    {
        $form = $event->getForm();

        foreach ($form->getLayout()->getFields() as $field) {
            if ($field instanceof PersistentValueInterface || !$field->getHandle()) {
                continue;
            }

            $field->setValue($field->getDefaultValue());
        }
    }
}
