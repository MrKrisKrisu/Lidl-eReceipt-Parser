<?php declare(strict_types=1);

use LidlParser\Parser;
use PHPUnit\Framework\TestCase;

final class ReceiptParsingTest extends TestCase {

    public function testParsing(): void {
        $receipt = Parser::parse(dirname(__DIR__) . '/tests/receipts/general.jpg');
        $this->assertEquals(7152, $receipt->getID());
        $this->assertEquals(1.04, $receipt->getTotal());
        $this->assertEquals("Thu Aug 13 2020 13:41:00 GMT+0200", $receipt->getTimestamp()->toString());
        $this->assertEquals("Kreditkarte", $receipt->getPaymentMethod());
        $this->assertCount(1, $receipt->getPositions());
    }
}
