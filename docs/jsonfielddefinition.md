---
sidebar_position: 3
---

# The JsonFieldDefinition

The `JsonFieldDefinition` class allows you to define how fields from a JSON file should be extracted, validated, and transformed.

## Creating a JsonFieldDefinition

```php title="Basic Usage"
use ByJG\AnyDataset\Json\JsonFieldDefinition;

$fieldDefinition = JsonFieldDefinition::create('field_name', 'json_path')
    ->withDefaultValue('default_value')
    ->required()
    ->ofTypeAny();
```

### Parameters

- **`field_name`**: The name of the field that will be used to access the value
- **`json_path`**: The path to the value in the JSON file. It can also be a closure to [create a dynamic field](dynamic-fields.md)
- **`default_value`**: The value that will be used if the field is not found in the JSON file. It keeps its PHP type (`0` stays an `int`, `false` a `bool`) and goes through the same type validation as a value read from the file
- **`required()`**: Will throw an exception if the field is not found or is null in the JSON file

## The Json Path

The `json_path` is a string that represents the path to the value in the JSON file. It defines the path to reach a specific key in the JSON file.

### Example

```json title="JSON Structure"
{
  "a": {
    "b": "SVG Viewer",
    "c": {
      "d": "Open"
    }
  }
}
```

**Path Examples:**
- The path to reach the value `"SVG Viewer"` is `a/b`
- The path to reach the value `"Open"` is `a/c/d`

## Working with Arrays

You can use the `*` character to iterate through arrays in the JSON path.

### Example

```json title="JSON with Arrays"
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

**Path Examples with Arrays:**
| Path | Result | Description |
|------|--------|-------------|
| `items/*/id` | `["item1", "item2"]` | All "id" values from items array |
| `items/0/values/*/name` | `["value1", "value2"]` | All "name" values in the first item |
| `items/*/values/*/name` | `[["value1", "value2"], ["value3"]]` | All "name" values in all items |

## Data Type Validation

You can validate and enforce data types for your fields using the following methods:

| Method | Description | Validation |
|--------|-------------|------------|
| `ofTypeAny()` | No validation | Accepts any value type |
| `ofTypeString()` | String validation | Value must be a string |
| `ofTypeInt()` | Integer validation | Value must be an integer, or a string/float holding a whole number (`0`, `"0"`, `"12"`, `5.0`). A string is read as decimal after trimming, leading zeros included (`"012"` and `" 12"` are `12`, never octal). `"12abc"`, `"1.9"`, `"1e3"`, hex and booleans are rejected. The result is an `int` |
| `ofTypeFloat()` | Float validation | Value must be a numeric value |
| `ofTypeBool()` | Boolean validation | Value must be a boolean |

:::warning
Type validation will throw an `InvalidArgumentException` if the value doesn't match the expected type.
:::

A typed field that is not `required()` can be absent: when it is missing and has no default value,
the field is `null` and the type is not checked. Use `required()` to reject a missing value.

