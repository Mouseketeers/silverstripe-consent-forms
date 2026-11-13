<?php

namespace Mouseketeers\ConsentForms\Tests;

use Mouseketeers\ConsentForms\ConsentCheckboxField;
use Mouseketeers\ConsentForms\ConsentRecorder;
use Mouseketeers\ConsentRecords\ConsentRecord;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class ConsentRecorderTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testRecordsOnlyTickedConsentFields()
    {
        // ConsentRecord reads the current request URL.
        $controller = Controller::create();
        $request = new HTTPRequest('GET', '/contact');
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->pushCurrent();

        $fields = FieldList::create(
            TextField::create('Email'),
            ConsentCheckboxField::create('Newsletter', 'Send me news'),
            ConsentCheckboxField::create('Offers', 'Send me offers')
        );

        $count = ConsentRecorder::record($fields, ['Email' => 'a@example.com', 'Newsletter' => 1]);

        $controller->popCurrent();
        $this->assertSame(1, $count);
        $this->assertSame(1, ConsentRecord::get()->count());
        $this->assertSame('a@example.com', ConsentRecord::get()->first()->ConsentID);
    }

    public function testSourceAndSubmissionAreStored()
    {
        $controller = Controller::create();
        $request = new HTTPRequest('GET', '/contact');
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->pushCurrent();

        ConsentRecorder::record(
            FieldList::create(ConsentCheckboxField::create('Newsletter', 'Send me news')),
            ['Newsletter' => 1],
            ['Source' => 'userform', 'SubmissionID' => 42]
        );

        $controller->popCurrent();
        $record = ConsentRecord::get()->first();
        $this->assertSame('userform', $record->Source);
        $this->assertSame(42, (int) $record->SubmissionID);
    }

    public function testNothingRecordedWhenNothingTicked()
    {
        $fields = FieldList::create(ConsentCheckboxField::create('Newsletter', 'Send me news'));

        $this->assertSame(0, ConsentRecorder::record($fields, []));
    }

    public function testFormDataIsNotStoredByDefault()
    {
        $this->recordWithEmail();

        $this->assertStringNotContainsString('secret', (string) ConsentRecord::get()->first()->ConsentData);
    }

    public function testFormDataIsStoredWhenEnabled()
    {
        \SilverStripe\Core\Config\Config::modify()->set(ConsentRecorder::class, 'store_form_data', true);

        $this->recordWithEmail();

        $this->assertStringContainsString('secret', (string) ConsentRecord::get()->first()->ConsentData);
    }

    private function recordWithEmail(): void
    {
        $controller = Controller::create();
        $request = new HTTPRequest('GET', '/contact');
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->pushCurrent();

        ConsentRecorder::record(
            FieldList::create(TextField::create('Message'), ConsentCheckboxField::create('Newsletter', 'Send me news')),
            ['Message' => 'secret', 'Newsletter' => 1]
        );

        $controller->popCurrent();
    }
}
