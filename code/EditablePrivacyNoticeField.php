<?php

class EditablePrivacyNoticeField extends EditableFormField {
	
	private static $singular_name = 'Privacy Notice';
	// Plural name is derived from the singular name and lang/*.yml PLURALNAME - no $plural_name needed.
	
    static $icon = 'consent-forms/images/privacy.png';
	
    public function populateDefaults() {
        parent::populateDefaults();
        $this->Title = self::$singular_name;
    }
	
	public function getFormField() {
        $field = new PrivacyNoticeField($this->Name);
		return $field;
	}
	public function getIcon() {
		return  self::$icon;
    }    
}