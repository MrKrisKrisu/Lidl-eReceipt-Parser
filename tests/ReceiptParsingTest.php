<?php declare(strict_types=1);

use Carbon\Carbon;
use LidlParser\Exception\PositionNotFoundException;
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
    public function getTotalWithoutMatchingTextThrowsException(): void
    {
        $this->expectException(ReceiptParseException::class);
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt('');
        $this->subject->getTotal();
    }

    /**
     * @test
     */
    public function getTotalWithMatchingPriceReturnsFloat(): void
    {
        // $subject = new Receipt('tests/receipt.jpg');
        $testText = 'zu zahlen 27,90';
        $this->subject->setRawReceipt($testText);
        $this->assertEquals(27.90, $this->subject->getTotal());
    }

    /**
     * @test
     */
    public function getPaymentMethodWithoutMatchingTextThrowsException(): void
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
    public function hasPayedCashlessReturnsCorrectBoolean(string $text, bool $expected): void
    {
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt($text);
        $this->assertEquals($expected, $this->subject->hasPayedCashless());
    }

    public function hasPayedCashlessProvider(): array
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
    public function getTimestampWithoutMatchingTextThrowsException(): void
    {
        $this->expectException(ReceiptParseException::class);
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt('');
        $this->subject->getTimestamp();
    }

    /**
     * @test
     */
    public function getTimestampReturnsCarbonDateObject(): void
    {
        $year = 21;
        $month = 11;
        $day = 10;
        $hour = 16;
        $minute = 30;
        // $subject = new Receipt('tests/receipt.jpg');
        $this->subject->setRawReceipt($day . '.' . $month . '.' . $year . ' ' . $hour . ':' . $minute);
        $expectedDate = Carbon::create('20' . $year, $month, $day, $hour, $minute);
        $this->assertEquals($expectedDate, $this->subject->getTimestamp());
    }

    /**
     * @test
     */
    public function getPositionsWithoutMatchingTextThrowsException(): void
    {
        $this->expectException(ReceiptParseException::class);
        $this->subject->setRawReceipt('');
        $this->subject->getPositions();
    }

    /**
     * @test
     */
    public function getPositionsReturnsArray(): void
    {
        $position1 = new \LidlParser\Position();
        $position1->setName('Black pepper');
        $position1->setPriceTotal(0.57);
        $position1->setTaxCode('A');

        $position2 = new \LidlParser\Position();
        $position2->setName('Donut');
        $position2->setPriceTotal(0.47);
        $position2->setTaxCode('B');

        $expectedResult[0] = $position1;
        $expectedResult[1] = $position2;

        $this->insertDataPositions();
        $this->assertEquals($expectedResult, $this->subject->getPositions());
    }

    /**
     * @test
     */
    public function getPositionByNameReturnsMatchingProduct(): void
    {
        $position1 = new \LidlParser\Position();
        $position1->setName('Black pepper');
        $position1->setPriceTotal(0.57);
        $position1->setTaxCode('A');

        $this->insertDataPositions();
        $this->assertEquals($position1, $this->subject->getPositionByName('Black pepper'));
    }

    /**
     * @test
     */
    public function getPositionByNameWithoutMatchingProductThrowsException(): void
    {
        $this->expectException(PositionNotFoundException::class);

        $this->insertDataPositions();
        $this->subject->getPositionByName('not_found');
    }

    protected function insertDataPositions(): void
    {
        $text = 'EUR' . "\n";
        $text .= 'Black pepper 0,57 A' . "\n";
        $text .= 'Donut 0,47 B' . "\n";
        $text .= 'zu zahlen 27,90';
        $this->subject->setRawReceipt($text);
    }
}
