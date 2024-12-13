<?php

namespace ByJG\AnyDataset\Json;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use Closure;
use InvalidArgumentException;
use ReturnTypeWillChange;

class JsonIterator extends GenericIterator
{

    /**
     * @var ?array
     */
    private ?array $jsonObject;

    /**
     * Enter description here...
     *
     * @var array
     */
    private array $current;

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
        $this->current = [
            'row' => null,
            'i' => 0,
        ];

        if (empty($path)) {
            $this->jsonObject = $jsonObject;
            return;
        }

        $this->jsonObject = $this->parseField($jsonObject, explode("/", ltrim("$path/*", "/")), null);
        if (is_null($this->jsonObject)) {
            if ($throwErr) {
                throw new IteratorException("Invalid path '$path' in JSON Object");
            }
            $this->jsonObject = [];
        }
    }

    private function parseRow(): ?RowInterface
    {
        if (!$this->valid()) {
            return null;
        }

        $rowNumber = $this->current["i"];
        $jsonObject = $this->jsonObject[$rowNumber];

        if (!empty($this->fieldDefinition)) {
            $valueList = [];
            $postProcessFields = [];
            /**
             * @var string $field
             * @var JsonFieldDefinition $value
             */
            foreach ($this->fieldDefinition as $field => $value) {
                if ($value->getPath() instanceof Closure) {
                    $postProcessFields[$field] = $value->getPath();
                    continue;
                }
                $pathList = explode("/", ltrim($value->getPath(), "/"));
                $valueList[$field] = $value->validate($this->parseField($jsonObject, $pathList, $value->getDefaultValue()));
            }

            foreach ($postProcessFields as $field => $callback) {
                $valueList[$field] = $callback($valueList);
            }
        } else {
            $valueList = $jsonObject;
        }

        $row = new RowArray($valueList);

        $this->current["row"] = $row;

        return $row;
    }

    private function parseField(array $record, array $pathList, mixed $defaultValue = null): mixed
    {
        $value = $record;
        while($pathElement = array_shift($pathList)) {
            if ($pathElement == "*") {
                $result = [];
                foreach ($value as $item) {
                    $parsedValue = $this->parseField($item, $pathList, $defaultValue);
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

    public function key(): int
    {
        return $this->current["i"];
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

    #[ReturnTypeWillChange]
    public function current(): ?RowInterface
    {
        return $this->current["row"] ?? $this->parseRow();
    }

    #[ReturnTypeWillChange]
    public function next(): void
    {
        $this->current["i"]++;
        $this->current["row"] = null;
        $this->parseRow();
    }

    #[ReturnTypeWillChange]
    public function valid(): bool
    {
        return ($this->current["i"] < count($this->jsonObject));
    }
}
