<?php

namespace Tests;

use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Core\RowInterface;
use ByJG\AnyDataset\Json\JsonDataset;
use ByJG\AnyDataset\Core\Row;
use Override;
use PHPUnit\Framework\TestCase;

class JsonDatasetTest extends TestCase
{

    const JSON_OK = '[{"name":"Joao","surname":"Magalhaes","age":"38"},{"name":"John","surname":"Doe","age":"20"},{"name":"Jane","surname":"Smith","age":"18"}]';
    const JSON_NOTOK = '"name":"Joao","surname":"Magalhaes","age":"38"}]';
    const JSON_OK2 = '{"menu": {"header": "SVG Viewer", "items": [ {"id": "Open"}, {"id": "OpenNew", "label": "Open New"} ]}}';

    protected array $arrTest = array();
    protected array $arrTest2 = array();

    // Run before each test case
    #[Override]
    public function setUp(): void
    {
        $this->arrTest = array();
        $this->arrTest[] = array("name" => "Joao", "surname" => "Magalhaes", "age" => 38);
        $this->arrTest[] = array("name" => "John", "surname" => "Doe", "age" => 20);
        $this->arrTest[] = array("name" => "Jane", "surname" => "Smith", "age" => 18);

        $this->arrTest2 = array();
        $this->arrTest2[] = array("id" => "Open");
        $this->arrTest2[] = array("id" => "OpenNew", "label" => "Open New");
    }

    public function testcreateJsonIterator()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator();

        $this->assertTrue($jsonIterator->hasNext()); // "hasNext() method must be true");
        $this->assertCount(3, $jsonIterator->toArray()); //, "Count() method must return 3");
    }

    public function testnavigateJsonIterator()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator();

        $count = 0;
        while ($jsonIterator->hasNext()) {
            $this->assertSingleRow($jsonIterator->moveNext(), $count++);
        }

        $this->assertEquals(3, $count); //, "Count() method must return 3");
    }

    public function testnavigateJsonIterator2()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK);
        $jsonIterator = $jsonDataset->getIterator();

        $count = 0;
        foreach ($jsonIterator as $sr) {
            $this->assertSingleRow($sr, $count++);
        }

        $this->assertEquals(3, $count); //, "Count() method must return 3");
    }

    public function testjsonNotWellFormatted()
    {
        $this->expectException(DatasetException::class);
        new JsonDataset(JsonDatasetTest::JSON_NOTOK);
    }

    public function navigateJSONComplex($path)
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK2);
        $jsonIterator = $jsonDataset->getIterator($path);

        $count = 0;
        foreach ($jsonIterator as $sr) {
            $this->assertSingleRow2($sr, $count++);
        }

        $this->assertEquals(2, $count); //, "Count() method must return 3");
    }

    public function testnavigateJSONComplexIterator()
    {
        $this->navigateJSONComplex("/menu/items");
    }

    public function testnavigateJSONComplexIteratorWithOutSlash()
    {
        $this->navigateJSONComplex("menu/items");
    }

    public function testnavigateJSONComplexIteratorWrongPath()
    {
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK2);
        $jsonIterator = $jsonDataset->getIterator("/menu/wrong");

        $this->assertEquals([], $jsonIterator->toArray()); //, "Without throw error");
    }

    public function testnavigateJSONComplexIteratorWrongPath2()
    {
        $this->expectException(IteratorException::class);
        $jsonDataset = new JsonDataset(JsonDatasetTest::JSON_OK2);
        $jsonDataset->getIterator("/menu/wrong", true);
    }

    /**
     * @param RowInterface $sr
     * @param int $count
     */
    public function assertSingleRow(RowInterface $sr, int $count)
    {
        $this->assertEquals($sr->get("name"), $this->arrTest[$count]["name"]);
        $this->assertEquals($sr->get("surname"), $this->arrTest[$count]["surname"]);
        $this->assertEquals($sr->get("age"), $this->arrTest[$count]["age"]);
    }

    /**
     * @param RowInterface $sr
     * @param int $count
     */
    public function assertSingleRow2(RowInterface $sr, int $count)
    {
        $this->assertEquals($sr->get("id"), $this->arrTest2[$count]["id"]);
        if ($count > 0) $this->assertEquals($sr->get("label"), $this->arrTest2[$count]["label"]);
    }
}
