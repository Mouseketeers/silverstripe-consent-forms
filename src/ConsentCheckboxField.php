<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\Forms\CheckboxField;


class ConsentCheckboxField extends CheckboxField {

	protected $consentIDFieldName = 'Email';
	protected $consentType = 'CustomConsent';
	protected $customValidationMessage;

	public function __construct($name, $title = null, $value = null) {
		// Only derive the consent type from the field name for generic
		// consent fields; subclasses (e.g. terms/privacy) keep their own type.
		if ($this->consentType === 'CustomConsent') {
			$this->setConsentType($name);
		}
		parent::__construct($name, $title, $value);
	}

	public function Type() {
		return 'checkbox';
	}
	public function setConsentIDFieldName($consentIDFieldName) {
		$this->consentIDFieldName = $consentIDFieldName;
		return $this;
	}
	public function getConsentIDFieldName() {
		return $this->consentIDFieldName;
	}
	public function getConsentType() {
		return $this->consentType;
	}
	public function setConsentType($consentType) {
		$this->consentType = $consentType;
		return $this;
	}
	public function getCustomValidationMessage() {
		return ($this->customValidationMessage) ? $this->customValidationMessage : _t('ConsentCheckboxField.ConsentErrorMessage', 'Please give your consent');
	}
	public function setCustomValidationMessage($message) {
		$this->customValidationMessage = $message;
		return $this;
	}
	public function validate($validator) {
		if (!$this->Value()) {
			$validator->validationError(
				$this->name,
				$this->getCustomValidationMessage(),
				"required"
			);
			return false;
		}
		return parent::validate($validator);
	}
}