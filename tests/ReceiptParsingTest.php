<?php declare(strict_types=1);

use LidlParser\Exception\ReceiptNotFoundException;
use LidlParser\Exception\ReceiptParseException;
use LidlParser\Receipt;
use PHPUnit\Framework\TestCase;

final class ReceiptParsingTest extends TestCase
{
    /**
     * @var Receipt
     */
    protected $subject;

    public function setUp(): void
    {
        // TODO Hier muss später ein Testbild hin
        $this->subject = new \LidlParser\Receipt('');
    }

    /**
     * @todo Später wieder aktivieren
     */
    public function constructorWithoutImagePathThrowsException(): void
    {
        $this->expectException(ReceiptNotFoundException::class);
        new Receipt('');
    }

    /**
     * @test
     */
    public function constructorWithFakePathThrowsException(): void
    {
        $this->expectException(ReceiptNotFoundException::class);
        new Receipt('fakepath');
    }

    /**
     * @test
     */
    public function getTotalWithoutMatchingTextThrowsException()
    {
        $this->expectException(ReceiptParseException::class);
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt('');
        $this->subject->getTotal();
    }

    /**
     * @test
     */
    public function getTotalWithMatchingPriceReturnsFloat()
    {
        // $subject = new Receipt('tests/receipt.jpg');
        $testText = 'zu zahlen 27,90';
        $this->subject->setRawReceipt($testText);
        $this->assertEquals(27.90, $this->subject->getTotal());
    }

    /**
     * @test
     */
    public function getPaymentMethodWithoutMatchingTextThrowsException()
    {
        $this->expectException(ReceiptParseException::class);
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt('');
        $this->subject->getPaymentMethod();
    }

    /**
     * @test
     * @dataProvider hasPayedCashlessProvider
     *
     * @param string $text
     * @param bool $expected
     */
    public function hasPayedCashlessReturnsCorrectBoolean(string $text, bool $expected)
    {
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt($text);
        $this->assertEquals($expected, $this->subject->hasPayedCashless());
    }

    public function hasPayedCashlessProvider()
    {
        return [
            ['Bar', false],
            ['Kreditkarte', true],
            ['Karte', true]
        ];
    }

    /**
     * @test
     */
    public function getTimestampWithoutMatchingTextThrowsException()
    {
        $this->expectException(ReceiptParseException::class);
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt('');
        $this->subject->getTimestamp();
    }
}
