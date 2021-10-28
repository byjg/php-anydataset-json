<?php

namespace Tests\AnyDataset\Json;

use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Json\JsonDataset;
use ByJG\AnyDataset\Core\Row;
use PHPUnit\Framework\TestCase;

class JsonDatasetWithFieldsTest extends TestCase
{

    const JSON_OK = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open", "metadata": {"version": "1", "date": "NA"} }, {"id": "OpenNew", "label": "Open New", "metadata": {"version": "2", "date": "2021-10-01"}} ]}}';

    protected $arrTest = array();

    // Run before each test case
    public function setUp()
    {
        $this->arrTest = array();
        $this->arrTest[] = array("name" => "Open", "version" => "1");
        $this->arrTest[] = array("name" => "OpenNew", "version" => "2");
    }

    // Run end each test case
    public function teardown()
    {

    }

    public function testcreateJsonIterator()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator("/menu/items")->withFields(["name" => "id", "version" => "metadata/version"]);

        $this->assertTrue($jsonIterator instanceof IteratorInterface); //, "Resultant object must be an interator");
        $this->assertTrue($jsonIterator->hasNext()); // "hasNext() method must be true");
        $this->assertEquals($jsonIterator->Count(), 2); //, "Count() method must return 2");
    }

    public function testnavigateJsonIterator()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator("/menu/items")->withFields(["name" => "id", "version" => "metadata/version"]);

        $count = 0;
        while ($jsonIterator->hasNext()) {
            $this->assertSingleRow($jsonIterator->moveNext(), $count++);
        }

        $this->assertEquals($jsonIterator->count(), 2); //, "Count() method must return 3");
    }

    public function testnavigateJsonIterator2()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator("/menu/items")->withFields(["name" => "id", "version" => "metadata/version"]);

        $count = 0;
        foreach ($jsonIterator as $sr) {
            $this->assertSingleRow($sr, $count++);
        }

        $this->assertEquals($jsonIterator->count(), 2); //, "Count() method must return 3");
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
