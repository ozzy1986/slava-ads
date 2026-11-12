<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Admin;

use PHPUnit\Framework\TestCase;
use SlavaAds\Admin\LeadCsvExporter;

final class LeadCsvExporterTest extends TestCase
{
    private function export(array $rows): string
    {
        $stream = fopen('php://memory', 'r+');
        (new LeadCsvExporter())->write($stream, $rows);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    public function testWritesBomHeaderAndRows(): void
    {
        $csv = $this->export([
            ['id' => 1, 'name' => 'Иван'],
            ['id' => 2, 'name' => 'Пётр'],
        ]);

        self::assertStringStartsWith("\xEF\xBB\xBF", $csv);
        self::assertStringContainsString('id,name', $csv);
        self::assertStringContainsString('1,Иван', $csv);
        self::assertStringContainsString('2,Пётр', $csv);
    }

    public function testEmptyRowsWritesOnlyBom(): void
    {
        self::assertSame("\xEF\xBB\xBF", $this->export([]));
    }
}
