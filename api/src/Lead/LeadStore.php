<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * Write side of lead persistence. Narrow on purpose (Interface
 * Segregation): the controller only needs to save.
 */
interface LeadStore
{
    public function save(Lead $lead): int;
}
