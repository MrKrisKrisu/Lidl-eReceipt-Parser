# Lidl eReceipt Parser

## Installation

```
$ composer require mrkriskrisu/lidl-ereceipt-parser
```

```json
{
    "require": {
        "mrkriskrisu/lidl-ereceipt-parser": "^0.1"
    }
}
```

## Example Usage
```php
<?php
require 'vendor/autoload.php';

use LidlParser\Parser;

$receipt = Parser::parse('receipt.jpg');

echo "You've paid " . $receipt->getTotal() . " Euros.";
```

## Requirements
This library requires Tesseract OCR v3.02 or later.

## Get the eReceipt
To receive the eReceipt you need do download the App "Lidl Plus". 
At your checkout you have to scan your customer card within the App 
and you'll can download the receipt in the app later.

## Contribution
I'm glad that you want to help this library to be perfect. 
Just do your magic und make a Pull Request. ✨
