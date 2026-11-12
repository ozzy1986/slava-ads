<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * Immutable lead aggregate. Holds sanitized data only; validation is a
 * separate concern (LeadValidator).
 */
final class Lead
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly string $message = '',
        public readonly string $quizRaw = '',
        public readonly string $source = '',
        public readonly string $page = '',
        public readonly string $referrer = '',
        public readonly Utm $utm = new Utm(),
        public readonly string $ip = '',
        public readonly string $userAgent = '',
        public readonly ?int $id = null,
    ) {
    }

    /** Return a copy with the persisted id assigned. */
    public function withId(int $id): self
    {
        return new self(
            $this->name,
            $this->phone,
            $this->message,
            $this->quizRaw,
            $this->source,
            $this->page,
            $this->referrer,
            $this->utm,
            $this->ip,
            $this->userAgent,
            $id,
        );
    }
}
