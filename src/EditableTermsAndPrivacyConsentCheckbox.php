<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\Forms\DropdownField;


class EditableTermsAndPrivacyConsentCheckbox extends EditableConsentCheckbox {

	private static $table_name = 'EditableTermsAndPrivacyConsentCheckbox';

	private static $singular_name = 'Terms and Privacy Consent Checkbox';

	private static $plural_name = 'Terms and Privacy Consent Checkboxes';

	static $icon = 'consent-forms/images/privacy.png';

	public function populateDefaults() {
		parent::populateDefaults();
		$this->Title = self::$singular_name;
	}

	public function getCMSFields() {
		$this->beforeUpdateCMSFields(function ($fields) {
			$fields->addFieldsToTab(
				'Root.Main',
				[
					DropdownField::create(
						'ConsentIDField',
						_t(__CLASS__ . '.ConsentIDField', 'Consent ID Field'),
						$this->Parent()->Fields()->map('Name', 'Title')->toArray()
					)
				]
			);
		});

		return parent::getCMSFields();
	}

	public function getFormField() {
		$consentID = $this->ConsentIDField;

		$field = TermsAndPrivacyConsentCheckboxField::create($this->Name)
			->setConsentIDFieldName($consentID);

		$errorMessage = ($this->getErrorMessage()) ? $this->getErrorMessage() : $field->getCustomValidationMessage();
		$field->setAttribute('data-rule-required', 'true');
		$field->setAttribute('data-msg-required', $errorMessage);

		return $field;
	}
}
