<?php

/**
 * Informational privacy notice rendered between the other form fields.
 *
 * Extends {@link LiteralField} because the notice outputs raw HTML, but renders
 * through the standard field holder template so it gets the same wrapper
 * (holder ID, CSS classes, middleColumn) as every other userforms field.
 * {@link LiteralField::FieldHolder()} bypasses the holder template entirely and
 * would output the notice without any of the field markup.
 */
class PrivacyNoticeField extends LiteralField {

	/**
	 * @param string $name
	 * @param string $content Optional notice text, defaults to the generated notice
	 */
	public function __construct($name = null, $content = null) {
		parent::__construct($name, $content ?: $this->getNoticeText());

		// The notice is not a question, so it must not render a label. FormField
		// would otherwise fall back to a label generated from the field name.
		// Clearing the title also adds the "nolabel" class via extraClass().
		$this->setTitle(null);
	}

	/**
	 * The notice text including a link to the privacy page selected in Site
	 * Settings, falling back to the configured privacy page URL.
	 *
	 * @return string
	 */
	public function getNoticeText() {
		$siteConfig = SiteConfig::current_site_config();
		$privacyLabel = _t('PrivacyNoticeField.PrivacyPolicy', 'Privacy Policy');
		$privacyLink = $siteConfig->getPrivacyPageLink($privacyLabel);

		$fullText = _t('PrivacyNoticeField.ImpliedAgreement');

		if ($privacyLink) {
			$fullText .= ' ' . _t(
				'PrivacyNoticeField.PrivacyPolicyReference',
				'For further details, please refer to our {privacypolicy}.',
				['privacypolicy' => $privacyLink]
			);
		}

		return $fullText;
	}

	/**
	 * The notice text itself. LiteralField::Field() delegates to FieldHolder(),
	 * which is overridden below, so it has to be defined here to avoid recursing
	 * back into the holder template.
	 *
	 * @param array $properties
	 * @return string
	 */
	public function Field($properties = array()) {
		return $this->getContent();
	}

	/**
	 * Render the notice inside the standard field holder, as
	 * {@link FormField::FieldHolder()} does for data fields.
	 *
	 * @param array $properties
	 * @return string
	 */
	public function FieldHolder($properties = array()) {
		$context = $this;

		if (count($properties)) {
			$context = $this->customise($properties);
		}

		return $context->renderWith($this->getFieldHolderTemplates());
	}
}
