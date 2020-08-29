<?php

namespace LidlParser;

use thiagoalessio\TesseractOCR\TesseractOcrException;

class Parser
{
    /**
     * @param string $imagePath
     * @return Receipt
     * @throws TesseractOcrException
     */
    public static function parse(string $imagePath): Receipt
    {
        return new Receipt($imagePath);
    }

}