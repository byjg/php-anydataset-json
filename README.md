# AnyDataset-Json

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/anydataset-json/)
[![GitHub license](https://img.shields.io/github/license/byjg/anydataset-json.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/anydataset-json.svg)](https://github.com/byjg/anydataset-json/releases/)
[![Build Status](https://travis-ci.com/byjg/anydataset-json.svg?branch=master)](https://travis-ci.com/byjg/anydataset-json)


JSON abstraction dataset. Anydataset is an agnostic data source abstraction layer in PHP. 

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

## Examples

### Simple Manipulation

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

### Specific Path

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

### Extracting Fields

```php
$json = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open", "metadata": {"version": "1", "date": "NA"} }, {"id": "OpenNew", "label": "Open New", "metadata": {"version": "2", "date": "2021-10-01"}} ]}}';

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator("/menu/items")->withFields(["name" => "id", "version" => "metadata/version"]);
foreach ($iterator as $row) {
    echo $row->get('name');       // Print "Open", "OpenNew"
    echo $row->get('version');    // Print "1", "2"
}
```

## Install

Just type: `composer require "byjg/anydataset-json=4.0.*"`

## Running the Unit tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
