---
sidebar_key: anydataset-json
sidebar_category: data-access
tags: [php, anydataset]
---

# JSON Dataset Abstraction

Read a JSON document as a table: point at a path, declare the fields, iterate the rows.

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-anydataset-json/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-json/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](https://opensource.byjg.com)
[![Install MCP Server](https://img.shields.io/badge/Install-MCP_Server-8A2BE2?logo=modelcontextprotocol&logoColor=white)](https://opensource.byjg.com/docs/ai/mcpserver-byjg-docs/)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-json/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-json.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-json.svg)](https://github.com/byjg/php-anydataset-json/releases/)

See more about Anydataset [here](https://opensource.byjg.com/anydataset).

## Why

`json_decode()` gives you nested arrays, and every consumer then writes its own loops,
`isset()` checks and casts to reach the data. When the JSON comes from an API, the shape
you depend on is scattered across that code.

JsonDataset declares it in one place. Point at the list with a path, declare each field
with its path, type and whether it is required, and read rows:

```php
$iterator = (new JsonDataset($json))
    ->getIterator('/menu/items')
    ->withFields([
        JsonFieldDefinition::create('name', 'id'),
        JsonFieldDefinition::create('version', 'metadata/version')->required()->ofTypeString(),
    ]);
```

The result is the same iterator of rows as every other
[AnyDataset](https://opensource.byjg.com/anydataset) source, so the code that reads it does
not care that the data was JSON.

## Concept

The AnyDataset-Json is an abstraction layer to read a JSON data and transform it into a dataset, 
and you can manipulate it as a table.

Some features:

 - Read a JSON file or string
 - Navigate through nested JSON structures using path notation
 - Define and extract fields with validation
 - Create dynamic fields using closures
 - Validate data types (string, int, float, bool)
 - Handle required fields and default values

### Basic Example

**example.json:**
```json title="example.json"
{
   "menu":{
      "header":"SVG Viewer",
      "items":[
         {
            "id":"Open",
            "metadata":{
               "version":"1",
               "date":"NA"
            }
         },
         {
            "id":"OpenNew",
            "label":"Open New",
            "metadata":{
               "version":"2",
               "date":"2021-10-01"
            }
         }
      ]
   }
}
```

**example.php:**
```php title="example.php"
$json = file_get_contents('example.json');

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

// Basic usage with field definitions
$iterator = $dataset->getIterator("/menu/items")
                        ->withFields([
                            \ByJG\AnyDataset\Json\JsonFieldDefinition::create("name", "id"),
                            \ByJG\AnyDataset\Json\JsonFieldDefinition::create("version", "metadata/version")
                                ->required()
                                ->ofTypeString()
                        ]);

foreach ($iterator as $row) {
    echo $row->get('name');       // Print "Open", "OpenNew"
    echo $row->get('version');    // Print "1", "2"
}
```

## Features

- [Simple Manipulation](docs/simple.md)
- [Creating dynamic fields](docs/dynamic-fields.md)
- [The JsonFieldDefinition](docs/jsonfielddefinition.md)


## Installation

```bash
composer require "byjg/anydataset-json"
```

## Running Tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](https://opensource.byjg.com)
