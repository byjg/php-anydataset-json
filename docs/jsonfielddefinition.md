---
sidebar_position: 3
---

# The JsonFieldDefinition

The JsonFieldDefinition is a class to define the fields of a JSON file.

### Creating a JsonFieldDefinition

```php
<?php
use ByJG\AnyDataset\Json\JsonFieldDefinition;

$fieldDefinition = JsonFieldDefinition::create('field_name', 'json_path')
    ->withDefaultValue('default_value')
    ->required()
    ->ofTypeAny();
```

- The `field_name` is the name of the field that will be used to access the value. 
- The `json_path` is the path to the value in the JSON file. Also, it can be a closure to [create a dynamic field](dynamic-fields.md).
- The `default_value` is the value that will be used if the field is not found in the JSON file.
- The `required()` method will throw an exception if the field is not found, or it is null in the JSON file.

### The Json Path

The `json_path` is a string that represents the path to the value in the JSON file. 
It defines the path to reach a specific key in the JSON file.

Example:

```json
{
  "a": {
    "b": "SVG Viewer",
    "c": {
      "d": "Open"
    }
  }
}
```

- The path to reach the value "SVG Viewer" is `a/b`
- The path to reach the value "Open" is `a/c/d`

### Working with Arrays

You can use the `*` character to iterate through arrays in the JSON path.

Example:

```json
{
  "items": [
    {
      "id": "item1",
      "values": [
        {"name": "value1"},
        {"name": "value2"}
      ]
    },
    {
      "id": "item2",
      "values": [
        {"name": "value3"}
      ]
    }
  ]
}
```

- The path to reach all "id" values is `items/*/id` (returns ["item1", "item2"])
- The path to reach all "name" values in the first item is `items/0/values/*/name` (returns ["value1", "value2"])
- The path to reach all "name" values in all items is `items/*/values/*/name` (returns [["value1", "value2"], ["value3"]])

## Validate the data type

These are the possible data types:

- `ofTypeAny()` - No validation
- `ofTypeString()` - The value must be a string
- `ofTypeInt()` - The value must be an integer
- `ofTypeFloat()` - The value must be a float
- `ofTypeBool()` - The value must be a boolean

