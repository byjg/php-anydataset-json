<?php

namespace Tests;

use ByJG\AnyDataset\Json\JsonDataset;
use ByJG\AnyDataset\Json\JsonFieldDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JsonFieldDefinitionTest extends TestCase
{
    public static function validIntProvider(): array
    {
        return [
            'int zero' => [0, 0],
            'string zero' => ["0", 0],
            'int' => [12, 12],
            'string' => ["12", 12],
            'negative string' => ["-3", -3],
            'whole float' => [5.0, 5],
            'leading zero' => ["012", 12],
            'negative leading zero' => ["-012", -12],
            'positive sign leading zero' => ["+012", 12],
            'leading zeros only' => ["000", 0],
            'not octal' => ["010", 10],
            'surrounding spaces' => [" 12 ", 12],
        ];
    }

    #[DataProvider('validIntProvider')]
    public function testIntAcceptsIntegers(mixed $value, int $expected): void
    {
        $this->assertSame($expected, JsonFieldDefinition::create('field', 'path')->ofTypeInt()->validate($value));
    }

    public static function invalidIntProvider(): array
    {
        return [
            'trailing garbage' => ["12abc"],
            'decimal string' => ["1.9"],
            'exponent string' => ["1e3"],
            'leading zero with garbage' => ["012abc"],
            'hex' => ["0x1A"],
            'sign only' => ["-"],
            'empty string' => [""],
            'overflow with leading zero' => ["0" . PHP_INT_MAX . "0"],
            'decimal float' => [1.9],
            'text' => ["abc"],
            'bool' => [true],
            'array' => [[1]],
        ];
    }

    #[DataProvider('invalidIntProvider')]
    public function testIntRejectsNonIntegers(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Field 'field' must be an integer");
        JsonFieldDefinition::create('field', 'path')->ofTypeInt()->validate($value);
    }

    public static function typedFieldProvider(): array
    {
        return [
            'int' => [JsonFieldDefinition::create('field', 'missing')->ofTypeInt()],
            'float' => [JsonFieldDefinition::create('field', 'missing')->ofTypeFloat()],
            'bool' => [JsonFieldDefinition::create('field', 'missing')->ofTypeBool()],
            'string' => [JsonFieldDefinition::create('field', 'missing')->ofTypeString()],
        ];
    }

    #[DataProvider('typedFieldProvider')]
    public function testMissingOptionalTypedFieldIsNull(JsonFieldDefinition $definition): void
    {
        $iterator = (new JsonDataset('[{"id": 1}]'))->getIterator()->withFields([$definition]);

        $this->assertNull($iterator->current()->get('field'));
    }

    #[DataProvider('typedFieldProvider')]
    public function testMissingRequiredTypedFieldStillFails(JsonFieldDefinition $definition): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Field 'field' is required");
        $definition->required()->validate(null);
    }

    public function testDefaultValueKeepsItsType(): void
    {
        $definition = JsonFieldDefinition::create('field', 'missing')->withDefaultValue(0)->ofTypeInt();
        $this->assertSame(0, $definition->getDefaultValue());

        $iterator = (new JsonDataset('[{"id": 1}]'))->getIterator()->withFields([$definition]);
        $this->assertSame(0, $iterator->current()->get('field'));
    }

    public function testBoolDefaultValueOnMissingField(): void
    {
        $definition = JsonFieldDefinition::create('field', 'missing')->withDefaultValue(false)->ofTypeBool();

        $iterator = (new JsonDataset('[{"id": 1}]'))->getIterator()->withFields([$definition]);
        $this->assertFalse($iterator->current()->get('field'));
    }
}
