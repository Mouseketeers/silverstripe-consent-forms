<?php

namespace Mouseketeers\ConsentForms;

use Mouseketeers\ConsentRecords\ConsentRecord;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;

/**
 * Collects checked consent fields from a submitted form and persists a
 * ConsentRecord for each one through consent-records' registerConsents() API.
 *
 * This lives in the core package (which does not depend on silverstripe/userforms)
 * so consents can be recorded from any SilverStripe form. The userforms add-on
 * (mouseketeers/silverstripe-consent-forms-userforms) feeds the submitted values
 * in from its UserDefinedFormController extension.
 */
class ConsentRecorder
{
    /**
     * Record one ConsentRecord for each checked consent field in the given form.
     *
     * @param FieldList $fields Rendered form fields (handles nested composite/step fields)
     * @param array $submitted Name => value map of the submitted fields
     * @return int Number of consent records written (0 when there is nothing to record)
     */
    public static function record(FieldList $fields, array $submitted)
    {
        $consentFields = [];
        $formData = [];

        foreach ($fields as $field) {
            self::categorise($field, $submitted, $consentFields, $formData);
        }

        if (empty($consentFields)) {
            return 0;
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
            return 0;
        }

        // One call per submission; a record is written for each consented field.
        ConsentRecord::registerConsents([
            'FormData' => $formData,
            'Consents' => $consents,
        ]);

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
