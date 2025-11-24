---
sidebar_position: 2
---

# Dynamic Fields

Dynamic fields are fields that are not defined in the dataset. They are created on the fly after reading the JSON file.

:::info
To define a dynamic field you need to use the `JsonFieldDefinition` class and the path needs to be a `\Closure`.
:::

## Example

**example.json:**
```json title="example.json"
{
   "menu":{
      "header":"SVG Viewer",
      "items":[
         {
            "id":"Open",
            "metadata":[
               {
                  "version":"1",
                  "date":"NA"
               },
               {
                  "version":"beta",
                  "date":"soon"
               }
            ]
         },
         {
            "id":"OpenNew",
            "label":"Open New",
            "metadata":[
               {
                  "version":"2",
                  "date":"2021-10-01"
               }
            ]
         }
      ]
   }
}
```

**example.php:**
```php title="example.php"
$json = file_get_contents('example.json');

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator("/menu/items")
                        ->withFields([
                            \ByJG\AnyDataset\Json\JsonFieldDefinition::create("name", "id"),
                            \ByJG\AnyDataset\Json\JsonFieldDefinition::create("version", "metadata/*/version"),
                            \ByJG\AnyDataset\Json\JsonFieldDefinition::create("dynamic", function($values) {
                               return $values["name"] . ":" . implode(", ", $values["version"]);
                            })
                        ]);

foreach ($iterator as $row) {
    echo $row->get('name');       // Print "Open", "OpenNew"
    echo $row->get('version');    // Print ["1", "Beta"], ["2"]
    echo $row->get('dynamic');    // Print "Open:1, Beta", "OpenNew:2"
}
```

:::tip
The closure will receive an array with all the values of the fields defined in the `withFields()` method. You can use this array to create the dynamic field.
:::
