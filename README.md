# AnyDataset-Json

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg.com-brightgreen.svg)](http://opensource.byjg.com)
[![Build Status](https://travis-ci.org/byjg/anydataset-json.svg?branch=master)](https://travis-ci.org/byjg/anydataset-json)


JSON abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

# Examples

## Simple Manipulation

```php
<?php
$json = '[{"name":"Joao","surname":"Magalhaes","age":"38"},{"name":"John","surname":"Doe","age":"20"},{"name":"Jane","surname":"Smith","age":"18"}]';

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('name');       // Print Joao, John, Jane
    echo $row->get('surname');    // Print Magalhaes, Doe, Smith
    echo $row->get('age');        // Print 38, 20, 18
}
```

## Specific Path

```php
<?php
$json = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open"}, {"id": "OpenNew", "label": "Open New"} ]}}';

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator("/menu/items");
foreach ($iterator as $row) {
    echo $row->get('id');       // Print "Open", "OpenNew"
    echo $row->get('label');    // Print "", "Open New"
}
```

# Install

Just type: `composer require "byjg/anydataset-json=4.0.*"`

# Running the Unit tests

```php
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
