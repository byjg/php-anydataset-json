<?php

namespace Tests;

use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Json\JsonDataset;
use ByJG\AnyDataset\Core\Row;
use PHPUnit\Framework\TestCase;

class JsonDatasetWithFieldsTest extends TestCase
{

    const JSON_OK = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open", "metadata": {"version": "1", "date": "NA"} }, {"id": "OpenNew", "label": "Open New", "metadata": {"version": "2", "date": "2021-10-01"}} ]}}';

    protected $iterator;
    protected $arrTest = array();

    // Run before each test case
    public function setUp(): void
    {
        $this->arrTest = array();
        $this->arrTest[] = array("name" => "Open", "version" => "1", "dynamic" => "Open:1");
        $this->arrTest[] = array("name" => "OpenNew", "version" => "2", "dynamic" => "OpenNew:2");

        $jsonDataset = new JsonDataset(JsonDatasetWithFieldsTest::JSON_OK);
        $this->iterator = $jsonDataset->getIterator("/menu/items")->withFields(["name" => "id", "version" => "metadata/version", "dynamic" => function ($values) {
            return $values["name"] . ":" . $values['version'];
        }]);
    }

    // Run end each test case
    public function teardown(): void
    {
        $this->iterator = null;
    }

    public function testcreateJsonIterator()
    {
        $this->assertTrue($this->iterator instanceof IteratorInterface); //, "Resultant object must be an interator");
        $this->assertTrue($this->iterator->hasNext()); // "hasNext() method must be true");
        $this->assertEquals(2, $this->iterator->Count()); //, "Count() method must return 2");
    }

    public function testnavigateJsonIterator()
    {
        $count = 0;
        while ($this->iterator->hasNext()) {
            $this->assertSingleRow($this->iterator->moveNext(), $count++);
        }

        $this->assertEquals(2, $this->iterator->count(), 2); //, "Count() method must return 2");
    }

    public function testnavigateJsonIterator2()
    {
        $this->assertEquals($this->arrTest, $this->iterator->toArray());
    }

    public function testArrayFieldDefinition()
    {
        $jsonDataset = new JsonDataset('{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open", "metadata": [{"version": "1", "date": "NA"}, {"version": "beta", "date": "soon"}] }, {"id": "OpenNew", "label": "Open New", "metadata": [{"version": "2", "date": "2021-10-01"}] } ]}}');

        $iterator = $jsonDataset->getIterator("/menu/items")->withFields(["name" => "id", "version" => "metadata/*/version"]);

        $this->assertEquals([
            ["name" => "Open", "version" => ["1", "beta"]],
            ["name" => "OpenNew", "version" => ["2"]]
        ], $iterator->toArray());
    }

    public function testNonExistentFieldDefinition()
    {
        $jsonDataset = new JsonDataset(JsonDatasetWithFieldsTest::JSON_OK);
        $iterator = $jsonDataset->getIterator("/menu/items")->withFields(["name" => "none", "version" => "metadata/version/nonexistantfield"]);

        $this->assertEquals([
            ["name" => null, "version" => null],
            ["name" => null, "version" => null]
        ], $iterator->toArray());
    }

    /**
     * @param Row $sr
     */
    public function assertSingleRow($sr, $count)
    {
        $this->assertEquals($sr->get("name"), $this->arrTest[$count]["name"]);
        $this->assertEquals($sr->get("version"), $this->arrTest[$count]["version"]);
    }
}
