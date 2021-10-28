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

        if ($path != "") {
            if ($path[0] == "/") {
                $path = substr($path, 1);
            }

            $pathAr = explode("/", $path);

            $newjsonObject = $jsonObject;

            foreach ($pathAr as $key) {
                if (array_key_exists($key, $newjsonObject)) {
                    $newjsonObject = $newjsonObject[$key];
                } elseif ($throwErr) {
                    throw new IteratorException("Invalid path '$path' in JSON Object");
                } else {
                    $newjsonObject = array();
                    break;
                }
            }
            $this->jsonObject = $newjsonObject;
        } else {
            $this->jsonObject = $jsonObject;
        }

        $this->current = 0;
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
        foreach ($this->fieldDefinition as $field => $path) {
            $pathList = preg_split("~/~", $path);
            $valueList[$field] = $this->parseField($jsonObject, $pathList);
        }

        return $valueList;
    }

    private function parseField($record, $pathList)
    {
        $value = $record;
        while($pathElement = array_shift($pathList)) {
            if ($pathElement == "*") {
                $result = [];
                foreach ($value as $item) {
                    $result[] = $this->parseField($item, $pathList);
                }
                $value = $result;
                break;
            }
            if (!isset($value[$pathElement])) {
                $value = null;
                break;
            }
            $value = $value[$pathElement];
        }
        return $value;
    }

    public function key()
    {
        return $this->current;
    }

    public function withFields($definition) {
        $this->fieldDefinition = $definition;
        return $this;
    }
}
