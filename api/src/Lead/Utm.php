<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * UTM attribution parameters grouped as one value object (DRY: the five
 * fields always travel together).
 */
final class Utm
{
    public function __construct(
        public readonly string $source = '',
        public readonly string $medium = '',
        public readonly string $campaign = '',
        public readonly string $content = '',
        public readonly string $term = '',
    ) {
    }

    /** @return array<string, string> Column-name keyed map for persistence. */
    public function toArray(): array
    {
        return [
            'utm_source' => $this->source,
            'utm_medium' => $this->medium,
            'utm_campaign' => $this->campaign,
            'utm_content' => $this->content,
            'utm_term' => $this->term,
        ];
    }

    /** Human-readable single line, skipping empty parts. */
    public function toLine(): string
    {
        $parts = array_filter([
            $this->source,
            $this->medium,
            $this->campaign,
            $this->content,
            $this->term,
        ], static fn (string $v): bool => $v !== '');

        return implode(' / ', $parts);
    }

    public function isEmpty(): bool
    {
        return $this->toLine() === '';
    }
}
