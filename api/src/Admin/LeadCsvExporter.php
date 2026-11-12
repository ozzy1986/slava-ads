<?php

declare(strict_types=1);

namespace SlavaAds\Admin;

/**
 * Writes lead rows as UTF-8 CSV (with BOM for Excel) to a stream.
 */
final class LeadCsvExporter
{
    /**
     * @param resource $stream
     * @param list<array<string, mixed>> $rows
     */
    public function write($stream, array $rows): void
    {
        // UTF-8 BOM so Excel detects the encoding.
        fwrite($stream, "\xEF\xBB\xBF");

        if ($rows === []) {
            return;
        }

        fputcsv($stream, array_keys($rows[0]), ',', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($stream, array_map(static fn ($v): string => (string) $v, $row), ',', '"', '\\');
        }
    }
}
