<?php

namespace Mouseketeers\ConsentForms;

use Mouseketeers\ConsentRecords\ConsentRecord;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;

/**
 * Collects checked consent fields from a submitted form and persists a
 * ConsentRecord for each one through consent-records' registerConsents() API.
 *
 * This lives in the core package (which does not depend on silverstripe/userforms)
 * so consents can be recorded from any SilverStripe form. The userforms add-on
 * (mouseketeers/silverstripe-consent-userforms) feeds the submitted values
 * in from its UserDefinedFormController extension.
 */
class ConsentRecorder
{
    /**
     * Whether the whole submission is copied into every ConsentRecord's
     * ConsentData. Off by default (data minimisation): the record then holds
     * only the consent itself, its ID, statement, type and URL.
     *
     * @config
     */
    private static $store_form_data = false;

    /**
     * Record one ConsentRecord for each checked consent field in the given form.
     *
     * @param FieldList $fields Rendered form fields (handles nested composite/step fields)
     * @param array $submitted Name => value map of the submitted fields
     * @param array $context Optional 'Source' (default 'form') and 'SubmissionID' stored on each record
     * @return int Number of consent records written (0 when there is nothing to record,
     *             or when saving failed; failures are logged)
     */
    public static function record(FieldList $fields, array $submitted, array $context = [])
    {
        $consentFields = [];
        $formData = [];

        foreach ($fields as $field) {
            self::categorise($field, $submitted, $consentFields, $formData);
        }

        if (empty($consentFields)) {
            return 0;
        }

        $storedData = Config::inst()->get(self::class, 'store_form_data') ? $formData : [];

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
                'ConsentData' => $storedData ?: null,
                'Source' => $context['Source'] ?? 'form',
                'SubmissionID' => $context['SubmissionID'] ?? 0,
            ];
        }

        if (empty($consents)) {
            return 0;
        }

        // One call per submission; a record is written for each consented field.
        $saved = ConsentRecord::registerConsents([
            'FormData' => $storedData,
            'Consents' => $consents,
        ]);

        if (!$saved) {
            Injector::inst()->get(LoggerInterface::class)->error(sprintf(
                'Consent records were NOT saved for %d consent(s) (%s).',
                count($consents),
                implode(', ', array_column($consents, 'ConsentType'))
            ));
            return 0;
        }

        return count($consents);
    }

    /**
     * Recursively split rendered fields into consent fields and regular form
     * data, descending into composite fields (steps, groups, selection groups).
     */
    protected static function categorise($field, array $submitted, array &$consentFields, array &$formData)
    {
        if ($field instanceof ConsentCheckboxField) {
            $consentFields[] = $field;
            return;
        }

        if ($field instanceof CompositeField) {
            foreach ($field->getChildren() as $child) {
                self::categorise($child, $submitted, $consentFields, $formData);
            }
            return;
        }

        $name = $field->getName();
        if ($name !== null && array_key_exists($name, $submitted)) {
            $formData[$name] = $submitted[$name];
        }
    }
}
