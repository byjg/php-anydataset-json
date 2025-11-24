---
sidebar_position: 1
---

# Simple Manipulation

This is the simplest way to manipulate a JSON file. You can read the JSON file and iterate over the rows without explicitly defining fields.

:::info
When your JSON is a simple array of objects, you can iterate directly without specifying a path or field definitions.
:::

## Example with a Simple JSON Array

**example1.json:**
```json title="example1.json"
[
   {
      "name":"Joao",
      "surname":"Magalhaes",
      "age":"38"
   },
   {
      "name":"John",
      "surname":"Doe",
      "age":"20"
   },
   {
      "name":"Jane",
      "surname":"Smith",
      "age":"18"
   }
]
```

**PHP Code:**

```php title="example1.php"
$json = file_get_contents('example1.json');

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('name');       // Print Joao, John, Jane
    echo $row->get('surname');    // Print Magalhaes, Doe, Smith
    echo $row->get('age');        // Print 38, 20, 18
}
```

:::tip
In this example, it's not necessary to define the fields because the fields are defined by the JSON file itself. The iterator automatically uses the keys from each object in the array.
:::

## Example with Nested JSON

For nested JSON structures, you can specify a path to the array you want to iterate.

**example2.json:**
```json title="example2.json"
{
  "users": {
    "active": [
      {
        "name":"Joao",
        "surname":"Magalhaes",
        "age":"38"
      },
      {
        "name":"John",
        "surname":"Doe",
        "age":"20"
      }
    ]
  }
}
```

**PHP Code:**

```php title="example2.php"
$json = file_get_contents('example2.json');

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator("users/active");
foreach ($iterator as $row) {
    echo $row->get('name');       // Print Joao, John
    echo $row->get('surname');    // Print Magalhaes, Doe
    echo $row->get('age');        // Print 38, 20
}
```

:::note
The path `"users/active"` tells the iterator to process the array found at that location in the JSON structure.
:::