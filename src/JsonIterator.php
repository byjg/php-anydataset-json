<?php

namespace ByJG\AnyDataset\Json;

use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use InvalidArgumentException;

class JsonIterator extends GenericIterator
{

    /**
     * @var array
     */
    private $jsonObject;

    /**
     * Enter description here...
     *
     * @var int
     */
    private $current = 0;

    private $fieldDefinition = [];

    /**
     * JsonIterator constructor.
     *
     * @param $jsonObject
     * @param string $path
     * @param bool $throwErr
     * @throws IteratorException
     */
    public function __construct($jsonObject, $path = "", $throwErr = false)
    {
        if (!is_array($jsonObject)) {
            throw new InvalidArgumentException("Invalid JSON object");
        }

        $this->current = 0;

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

    public function count()
    {
        return (count($this->jsonObject));
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->current < $this->count()) {
            return true;
        }

        return false;
    }

    /**
     * @access public
     * @return Row
     * @throws IteratorException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        }

        return new Row($this->parseFields($this->jsonObject[$this->current++]));
    }

    private function parseFields($jsonObject) {
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
            if ($value->getPath() instanceof \Closure) {
                $postProcessFields[$field] = $value->getPath();
                continue;
            }
            $pathList = explode("/", ltrim($value->getPath(), "/"));
            $valueList[$field] = $value->validate($this->parseField($jsonObject, $pathList, $value->getDefaultValue()));
        }

        foreach ($postProcessFields as $field => $callback) {
            $valueList[$field] = $callback($valueList);
        }

        return $valueList;
    }

    /**
     * @param mixed $record
     * @param mixed $pathList
     * @param mixed $defaultValue
     * @return mixed|null
     */
    private function parseField($record, $pathList, $defaultValue)
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

    protected function validateValueAgainstFieldDefinition($value, $fieldDefinition)
    {
    }

    public function key()
    {
        return $this->current;
    }

    public function withFields($definition) {
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
