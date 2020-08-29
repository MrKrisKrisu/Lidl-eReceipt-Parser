<?php

use LidlParser\Exception\ReceiptParseException;
use LidlParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOcrException;

require_once '../vendor/autoload.php';

try {
    $receipt = Parser::parse('../tests/receipts/receipt.jpg');

    echo "The receipt was made " . $receipt->getTimestamp()->diffForHumans() . ". \n";
    echo "You've bought " . count($receipt->getPositions()) . " Products for a total of " . $receipt->getTotal() . "€. \n";

} catch (ReceiptParseException $e) {
    echo "There is something weird with the receipt... Maybe it's not compatible?\n";
    echo "Error: " . $e->getMessage();
} catch (TesseractOcrException $e) {
    echo "The given Image cant be read successfully: " . $e->getMessage();
}
