# Simple Manipulation

This is the simplest way to manipulate a JSON file. You can read the JSON file and iterate over the rows.

```json
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

Here is the code

```php
<?php
$json = file_get_contents('example1.json');

$dataset = new \ByJG\AnyDataset\Json\JsonDataset($json);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('name');       // Print Joao, John, Jane
    echo $row->get('surname');    // Print Magalhaes, Doe, Smith
    echo $row->get('age');        // Print 38, 20, 18
}
```

This example is not necessary to define the fields because the fields are defined by the JSON file itself.