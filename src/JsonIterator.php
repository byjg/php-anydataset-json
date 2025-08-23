<?php

namespace ByJG\AnyDataset\Json;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use Closure;
use InvalidArgumentException;
use Override;
use ReturnTypeWillChange;

class JsonIterator extends GenericIterator
{

    /**
     * @var ?array
     */
    private ?array $jsonObject;

    private ?RowInterface $currentRow = null;
    private int $currentIndex = 0;

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

        $jsonObject = $this->getJsonObjectForCurrentRow();
        $valueList = $this->parseFields($jsonObject);

        $row = new RowArray($valueList);
        $this->currentRow = $row;
        return $row;
    }

    /**
     * Retrieve the JSON object for the current row index.
     */
    private function getJsonObjectForCurrentRow(): ?array
    {
        return $this->jsonObject[$this->currentIndex] ?? null;
    }

    /**
     * Parse the fields of the given JSON object based on field definitions, including post-processing.
     */
    private function parseFields(array $jsonObject): array
    {
        if (empty($this->fieldDefinition)) {
            return $jsonObject;
        }

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
            $valueList[$field] = $value->validate(
                $this->parseField($jsonObject, $pathList, $value->getDefaultValue())
            );
        }

        foreach ($postProcessFields as $field => $callback) {
            $valueList[$field] = $callback($valueList);
        }

        return $valueList;
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

    #[ReturnTypeWillChange]
    #[Override]
    public function key(): int
    {
        return $this->currentIndex;
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
    #[Override]
    public function current(): ?RowInterface
    {
        return $this->currentRow ?? $this->parseRow();
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function next(): void
    {
        $this->currentIndex++;
        $this->currentRow = null;
        // Eagerly parse the next row to trigger validation and potential exceptions
        // so that errors are raised at iteration time (as tests expect).
        $this->parseRow();
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function valid(): bool
    {
        return ($this->currentIndex < count($this->jsonObject));
    }
}
