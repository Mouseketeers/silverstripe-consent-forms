<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CompositeField;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use Mouseketeers\ConsentRecords\ConsentRecord;

/**
 * Persists consent records after a UserDefinedForm is successfully submitted.
 *
 * Registered on {@link UserDefinedFormController} (not RequestHandler) so it
 * never runs for ordinary pages, login or error controllers that also handle
 * a `Form` action. The hook is fired once per successfully validated submission
 * with the rendered form fields rather than the editable CMS field models.
 */
class ConsentFormExtension extends Extension {

	/**
	 * @param array $emailData
	 * @param array $attachments
	 */
	public function updateEmailData($emailData, $attachments) {

		$controller = $this->owner;
		if (!$controller instanceof UserDefinedFormController) {
			return;
		}

		$form = $controller->Form();
		if (!$form) {
			return;
		}

		// Build a name => value map from the submitted fields.
		$submitted = [];
		foreach ($emailData['Fields'] ?? [] as $submittedField) {
			// Older UserForms versions do not have a Displayed flag.
			// Where available, exclude fields hidden by conditional rules.
			if ($submittedField->hasField('Displayed') && !$submittedField->Displayed) {
				continue;
			}

			if ($submittedField->Name) {
				$submitted[$submittedField->Name] = $submittedField->Value;
			}
		}

		// Walk rendered form fields (handling nested composite/step fields)
		// to collect consent fields and remaining form data.
		$consentFields = [];
		$formData = [];
		foreach ($form->Fields() as $field) {
			$this->categoriseFields($field, $submitted, $consentFields, $formData);
		}

		if (empty($consentFields)) {
			return;
		}

		$consents = [];
		foreach ($consentFields as $field) {
			// Only checked consent checkboxes are recorded.
			if (empty($submitted[$field->getName()])) {
				continue;
			}

			$consents[] = [
				'ConsentType' => $field->getConsentType(),
				'ConsentID' => $submitted[$field->getConsentIDFieldName()] ?? null,
				'ConsentStatement' => $field->Title(),
				'ConsentData' => $formData,
			];
		}

		if (empty($consents)) {
			return;
		}

		// One call per submission; a record is written for each consented field.
		ConsentRecord::registerConsents([
			'FormData' => $formData,
			'Consents' => $consents,
		]);
	}

	/**
	 * Recursively split rendered fields into consent fields and regular form
	 * data, descending into composite fields (steps, groups, selection groups).
	 *
	 * @param mixed $field
	 * @param array $submitted
	 * @param array $consentFields
	 * @param array $formData
	 */
	protected function categoriseFields($field, array $submitted, array &$consentFields, array &$formData) {

		if ($field instanceof ConsentCheckboxField) {
			$consentFields[] = $field;
			return;
		}

		if ($field instanceof CompositeField) {
			foreach ($field->getChildren() as $child) {
				$this->categoriseFields($child, $submitted, $consentFields, $formData);
			}
			return;
		}

		$name = $field->getName();
		if ($name !== null && array_key_exists($name, $submitted)) {
			$formData[$name] = $submitted[$name];
		}
	}
}
