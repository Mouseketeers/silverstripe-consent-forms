<?php

namespace Mouseketeers\ConsentForms\Tests;

use Mouseketeers\ConsentForms\ConsentCheckboxField;
use SilverStripe\Dev\SapphireTest;

class ConsentCheckboxFieldTest extends SapphireTest
{
    public function testUntickedBoxFailsValidationWithMessage()
    {
        $field = ConsentCheckboxField::create('Newsletter', 'Send me news');
        $field->setCustomValidationMessage('Tick it');

        $result = $field->validate();

        $this->assertFalse($result->isValid());
        $this->assertSame('Tick it', $result->getMessages()[0]['message']);
    }

    public function testTickedBoxPassesValidation()
    {
        $field = ConsentCheckboxField::create('Newsletter', 'Send me news');
        $field->setValue(1);

        $this->assertTrue($field->validate()->isValid());
    }

    public function testOptionalBoxPassesWhenUnticked()
    {
        $field = ConsentCheckboxField::create('Newsletter', 'Send me news')->setConsentRequired(false);

        $this->assertTrue($field->validate()->isValid());
    }
}
