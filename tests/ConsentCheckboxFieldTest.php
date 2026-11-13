<?php

namespace Mouseketeers\ConsentForms\Tests;

use Mouseketeers\ConsentForms\ConsentCheckboxField;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\RequiredFields;

class ConsentCheckboxFieldTest extends SapphireTest
{
    public function testUntickedBoxFailsValidationWithMessage()
    {
        $field = ConsentCheckboxField::create('Newsletter', 'Send me news');
        $field->setCustomValidationMessage('Tick it');
        $validator = new RequiredFields();

        $this->assertFalse($field->validate($validator));
        $this->assertSame('Tick it', $validator->getErrors()[0]['message']);
    }

    public function testTickedBoxPassesValidation()
    {
        $field = ConsentCheckboxField::create('Newsletter', 'Send me news');
        $field->setValue(1);

        $this->assertTrue($field->validate(new RequiredFields()));
    }
}
