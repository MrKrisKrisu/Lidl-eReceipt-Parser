<?php

namespace LidlParser;

use Carbon\Carbon;
use LidlParser\Exception\PositionNotFoundException;
use LidlParser\Exception\ReceiptParseException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;

class Receipt {
    private $rawReceipt;
    private $explodedReceipt;

    /**
     * @param string $imagePath
     * @throws TesseractOcrException
     */
    public function __construct(string $imagePath) {
        $ocr                   = new TesseractOCR($imagePath);
        $this->rawReceipt      = $ocr->run();
        $this->rawReceipt      = str_replace('@', '0', $this->rawReceipt); //Maybe there is a better solution to handle these ocr problem?
        $this->explodedReceipt = explode("\n", $this->rawReceipt);
    }

    /**
     * @throws ReceiptParseException
     */
    public function getID(): int {
        if(preg_match('/(\d+) (\d+)\/(\d+) (\d{2}).(\d{2})/', $this->rawReceipt, $match)) {
            return (int)$match[1];
        }
        throw new ReceiptParseException();
    }

    /**
     * @return float
     * @throws ReceiptParseException
     */
    public function getTotal(): float {
        if(preg_match('/zu zahlen (-?\d+,\d{2})/', $this->rawReceipt, $match))
            return (float)str_replace(',', '.', $match[1]);
        throw new ReceiptParseException();
    }

    /**
     * @return string
     * @throws ReceiptParseException
     */
    public function getPaymentMethod(): string {
        $next = false;
        foreach($this->explodedReceipt as $row)
            if($next) {
                if(!preg_match("/(.*) \d+,\d{2}/", $row, $match))
                    throw new ReceiptParseException();
                return $match[1];
            } else if(substr(trim($row), 0, 9) == "zu zahlen")
                $next = true;
        throw new ReceiptParseException();
    }

    /**
     * @return bool
     */
    public function hasPayedCashless(): bool {
        return preg_match('/(Kreditkarte|Karte)/', $this->rawReceipt);
    }

    /**
     * @return Carbon
     * @throws ReceiptParseException
     */
    public function getTimestamp(): Carbon {
        if(preg_match('/(\d{2}).(\d{2}).(\d{2}) (\d{2}):(\d{2})/', $this->rawReceipt, $match)) {
            return Carbon::create("20" . $match[3], $match[2], $match[1], $match[4], $match[5], 0, 'Europe/Berlin');
        }
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    private function getProductStartLine(): int {
        foreach(explode("\n", $this->rawReceipt) as $line => $content)
            if(trim($content) == "EUR")
                return $line + 1;
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    private function getProductEndLine(): int {
        foreach(explode("\n", $this->rawReceipt) as $line => $content)
            if(substr(trim($content), 0, 9) == "zu zahlen")
                return $line - 1;
        throw new ReceiptParseException();
    }

    /**
     * @param string $name
     * @return Position
     * @throws PositionNotFoundException|ReceiptParseException
     */
    public function getPositionByName(string $name): Position {
        foreach($this->getPositions() as $position) {
            if($position->getName() == $name)
                return $position;
        }
        throw new PositionNotFoundException("Position '$name' not found");
    }

    /**
     * TODO: Wiege und mehrzahl
     * @return array
     * @throws ReceiptParseException
     */
    public function getPositions(): array {
        $positions    = [];
        $lastPosition = NULL;

        for($lineNr = $this->getProductStartLine(); $lineNr <= $this->getProductEndLine(); $lineNr++) {
            //echo $this->explodedReceipt[$lineNr];
            if($this->isProductLine($lineNr)) {

                if($lastPosition !== NULL) {
                    $positions[]  = $lastPosition;
                    $lastPosition = NULL;
                }

                if(preg_match('/(.*) (-?\d+,\d{2}) ([A-Z])/', $this->explodedReceipt[$lineNr], $match)) {
                    $lastPosition = new Position();
                    $lastPosition->setName(trim($match[1]));
                    $lastPosition->setPriceTotal((float)str_replace(',', '.', $match[2]));
                    $lastPosition->setTaxCode($match[3]);
                } elseif(preg_match('/(.*) (-?\d+,\d{2})/', $this->explodedReceipt[$lineNr], $match)) {
                    $lastPosition = new Position();
                    $lastPosition->setName(trim($match[1]));
                    $lastPosition->setPriceTotal((float)str_replace(',', '.', $match[2]));
                } else throw new ReceiptParseException("Error while parsing Product line");

            } /*else if ($this->isAmountLine($lineNr)) {

                if (preg_match('/(-?\d+) Stk x *(-?\d+,\d{2})/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition->setAmount((int)$match[1]);
                    $lastPosition->setPriceSingle((float)str_replace(',', '.', $match[2]));
                } else throw new ReceiptParseException("Error while parsing Amount line");

            } else if ($this->isWeightLine($lineNr)) {

                if (preg_match('/(-?\d+,\d{3}) kg x *(-?\d+,\d{2}) EUR/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition->setWeight((float)str_replace(',', '.', $match[1]));
                    $lastPosition->setPriceSingle((float)str_replace(',', '.', $match[2]));
                } else if (preg_match('/Handeingabe E-Bon *(-?\d+,\d{3}) kg/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition->setWeight((float)str_replace(',', '.', $match[1]));
                } else throw new ReceiptParseException("Error while parsing Weight line");

            }*/ else throw new ReceiptParseException("Error while parsing unknown receipt line");

        }

        if($lastPosition !== NULL)
            $positions[] = $lastPosition;

        if(count($positions) == 0)
            throw new ReceiptParseException("Cannot parse any products on receipt");

        return $positions;
    }

    private function isWeightLine($lineNr) {
        return false; //TODO: Receipt example needed
        return strpos($this->expl_receipt[$lineNr], 'kg') !== false;
    }

    private function isAmountLine($lineNr) {
        return false; //TODO: Receipt example needed
        return strpos($this->expl_receipt[$lineNr], ' Stk x') !== false;
    }

    private function isProductLine($lineNr) {
        return true; //TODO: Receipt example needed
        return !$this->isWeightLine($lineNr) && !$this->isAmountLine($lineNr);
    }
}