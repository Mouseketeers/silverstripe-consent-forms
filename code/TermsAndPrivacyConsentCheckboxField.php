<?php

class TermsAndPrivacyConsentCheckboxField extends ConsentCheckboxField {

    protected $consentType = 'TermsAndPrivacyConsent';

	public function __construct($name = null, $title = null, $value = null) {
        $name = $name ?: $this->consentType;
        $title = $title ?: $this->getTitle();
		parent::__construct($name, $title, $value);
	}
    
    public function getTitle() {
        $siteConfig = SiteConfig::current_site_config();
        
        $termsLabel = _t('TermsAndPrivacyConsentCheckboxField.Terms', 'Terms of Service');
        $privacyLabel = _t('TermsAndPrivacyConsentCheckboxField.PrivacyPolicy', 'Privacy Policy');

        $terms = $siteConfig->getTermsPageLink($termsLabel) ?: $termsLabel;
        $privacy = $siteConfig->getPrivacyPageLink($privacyLabel) ?: $privacyLabel;
        
        return _t(
            'TermsAndPrivacyConsentCheckboxField.ConsentStatement',
            'I agree to the {terms} and acknowledge the {privacypolicy}.',
            [
                'terms' => $terms,
                'privacypolicy' => $privacy
            ]
        );
    }
}