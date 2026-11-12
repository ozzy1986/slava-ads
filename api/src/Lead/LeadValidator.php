<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * Validates a Lead's required fields. Returns user-facing error messages;
 * an empty array means the lead is valid.
 */
final class LeadValidator
{
    private const MIN_NAME_LENGTH = 2;
    private const MIN_PHONE_DIGITS = 5;

    /** @return list<string> */
    public function validate(Lead $lead): array
    {
        $errors = [];

        if (mb_strlen($lead->name) < self::MIN_NAME_LENGTH) {
            $errors[] = 'Введите имя';
        }

        $phoneChars = preg_replace('/\s+/', '', $lead->phone) ?? '';
        if (mb_strlen($phoneChars) < self::MIN_PHONE_DIGITS) {
            $errors[] = 'Введите телефон или контакт';
        }

        return $errors;
    }

    public function isValid(Lead $lead): bool
    {
        return $this->validate($lead) === [];
    }
}
