<?php

namespace ByJG\AnyDataset\Json;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use Closure;
use Generator;
use InvalidArgumentException;
use Override;

class JsonIterator extends GenericIterator
{

    /**
     * @var array
     */
    private array $jsonObject;

    private array $fieldDefinition = [];

    /**
     * JsonIterator constructor.
     *
     * @param array $jsonObject
     * @param string $path
     * @param bool $throwErr
     * @throws IteratorException
     */
    public function __construct(array $jsonObject, string $path = "", bool $throwErr = false)
    {
        if (empty($path)) {
            $this->jsonObject = $jsonObject;
            return;
        }

        $parsedObject = self::parseField($jsonObject, explode("/", ltrim("$path/*", "/")), null);
        if (is_null($parsedObject)) {
            if ($throwErr) {
                throw new IteratorException("Invalid path '$path' in JSON Object");
            }
            $this->jsonObject = [];
        } else {
            $this->jsonObject = $parsedObject;
        }
    }

    #[Override]
    protected function rows(): Generator
    {
        return self::walk($this->jsonObject, $this->fieldDefinition);
    }

    /**
     * @param array<string, JsonFieldDefinition> $fieldDefinition
     */
    private static function walk(array $jsonObject, array $fieldDefinition): Generator
    {
        foreach ($jsonObject as $record) {
            yield is_array($record) ? new RowArray(self::parseFields($record, $fieldDefinition)) : null;
        }
    }

    /**
     * Parse the fields of the given JSON object based on field definitions, including post-processing.
     */
    private static function parseFields(array $jsonObject, array $fieldDefinition): array
    {
        if (empty($fieldDefinition)) {
            return $jsonObject;
        }

        $valueList = [];
        $postProcessFields = [];
        /**
         * @var string $field
         * @var JsonFieldDefinition $value
         */
        foreach ($fieldDefinition as $field => $value) {
            $path = $value->getPath();
            if ($path instanceof Closure) {
                $postProcessFields[$field] = $path;
                continue;
            }
            if ($path === null) {
                continue;
            }
            $pathList = explode("/", ltrim($path, "/"));
            $valueList[$field] = $value->validate(
                self::parseField($jsonObject, $pathList, $value->getDefaultValue())
            );
        }

        foreach ($postProcessFields as $field => $callback) {
            $valueList[$field] = $callback($valueList);
        }

        return $valueList;
    }

    private static function parseField(array $record, array $pathList, mixed $defaultValue = null): mixed
    {
        $value = $record;
        while($pathElement = array_shift($pathList)) {
            if ($pathElement == "*") {
                $result = [];
                foreach ($value as $item) {
                    $parsedValue = self::parseField($item, $pathList, $defaultValue);
                    if (!is_null($parsedValue)) {
                        $result[] = $parsedValue;
                    }
                }
                $value = $result;
                break;
            }
            if (!isset($value[$pathElement])) {
                $value = $defaultValue;
                break;
            }
            $value = $value[$pathElement];
        }

        return $value;
    }

    /**
     * @param array $definition
     * @return $this
     */
    public function withFields(array $definition): static
    {
        foreach ($definition as $field => $value) {
            if ($value instanceof JsonFieldDefinition) {
                $field = $value->getFieldName();
            } else {
                $value = JsonFieldDefinition::create($field, $value);
            }

            if (array_key_exists($field, $this->fieldDefinition)) {
                throw new InvalidArgumentException("Field '$field' already defined");
            }

            $this->fieldDefinition[$field] = $value;
        }
        return $this;
    }
}
