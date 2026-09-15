<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\FieldType\DBField;


class EditableConsentCheckbox extends EditableFormField {

	private static $table_name = 'EditableConsentCheckbox';
	
	private static $singular_name = 'Consent Checkbox';

	private static $plural_name = 'Consent Checkboxes';

	static $icon = 'consent-forms/images/privacy.png';

    private static $db = [
    	'ConsentIDField' => 'Varchar(255)'
    ];	

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    DropdownField::create(
                        'ConsentIDField',
                        _t(__CLASS__.'.ConsentIDField', 'Consent ID Field'),
                      	$this->Parent()->Fields()->map('Name', 'Title')->toArray()
                    )
                ]
            );
        });

        return parent::getCMSFields();
    }

	public function getFormField() 
	{
		
		$consentID = $this->ConsentIDField;

		$field = ConsentCheckboxField::create( $this->Name, $this->Title)
			->setConsentIDFieldName($consentID);
		
		$errorMessage = ($this->getErrorMessage()) ? $this->getErrorMessage() : $field->getCustomValidationMessage();
		$field->setAttribute('data-rule-required', 'true');
		$field->setAttribute('data-msg-required', $errorMessage);
		
		return $field;
	}
}