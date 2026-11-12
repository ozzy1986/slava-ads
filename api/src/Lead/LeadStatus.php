<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * Lifecycle states of a lead. Backed enum keeps status values type-safe
 * across the repository and admin panel.
 */
enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Sent = 'sent';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::Contacted => 'В работе',
            self::Sent => 'Передана',
            self::Rejected => 'Отклонена',
        };
    }

    public static function tryFromValue(?string $value): ?self
    {
        return $value === null ? null : self::tryFrom($value);
    }
}
