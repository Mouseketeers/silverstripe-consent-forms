<?php

class PrivacyNoticeField extends LiteralField {


	public function __construct($name = null, $title = null, $value = null) {
        $title = $title ?: $this->getTitle();
		parent::__construct($name, $title, $value);
	}
    
    public function getTitle() {
        $siteConfig = SiteConfig::current_site_config();
        $privacyLabel = _t('PrivacyNoticeField.PrivacyPolicy', 'Privacy Policy');
        $privacyLink = $siteConfig->getPrivacyPageLink($privacyLabel);
        
        $baseText = _t(
            'PrivacyNoticeField.ImpliedAgreement'
        );
        
        $fullText = $baseText;
        
        if ($privacyLink) {
            $privacyText = _t(
                'PrivacyNoticeField.PrivacyPolicyReference', 
                'For further details, please refer to our {privacypolicy}.',
                ['privacypolicy' => $privacyLink]
            );
            
            $fullText .= ' ' . $privacyText;
        }
        
        return '<div class="field">' . $fullText . '</div>';
    }
}