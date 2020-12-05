<?php declare(strict_types=1);

use LidlParser\Parser;
use PHPUnit\Framework\TestCase;

final class ReceiptParsingTest extends TestCase
{
    public function test_parsing()
    {
        $receipt = Parser::parse(dirname(__FILE__) . '/receipts/general.jpg');

        $this->assertEquals(1.04, $receipt->getTotal());
        $this->assertEquals("Thu Aug 13 2020 13:41:00 GMT+0200", $receipt->getTimestamp()->toString());
        $this->assertEquals("Kreditkarte", $receipt->getPaymentMethod());
        $this->assertCount(1, $receipt->getPositions());
    }
}
