<?php

namespace Mouseketeers\ConsentForms;

use SilverStripe\UserForms\Model\EditableFormField;


class EditablePrivacyNoticeField extends EditableFormField {

	private static $table_name = 'EditablePrivacyNoticeField';

	private static $singular_name = 'Privacy Notice';

	/**
	 * Singular name is used as the default title; the plural name comes from
	 * lang/*.yml PLURALNAME so no $plural_name is needed.
	 */
	static $icon = 'consent-forms/images/privacy.png';

	public function populateDefaults() {
		parent::populateDefaults();
		$this->Title = self::$singular_name;
	}

	public function getFormField() {
		return PrivacyNoticeField::create($this->Name);
	}
}
