<?php

namespace ByJG\AnyDataset\Json;

use InvalidArgumentException;

class JsonFieldDefinition
{
    /** @var string $fieldName */
    protected $fieldName;

    /** @var bool $required */
    protected $required = false;

    /** @var string $defaultValue */
    protected $defaultValue = null;

    /** @var string $type */
    protected $type = 'string';

    protected $path = null;

    const STRING = 'string';
    const INT = 'int';
    const FLOAT = 'float';
    const BOOL = 'bool';
    const ANY = 'any';

    public function __construct($fieldName, $path, $required = false, $defaultValue = null, $type = self::ANY)
    {
        $this
            ->withName($fieldName)
            ->withPath($path)
            ->required($required)
            ->withDefaultValue($defaultValue)
            ->ofType($type);
    }

    public static function create($fieldName, $path): self
    {
        return new self($fieldName, $path);
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getType()
    {
        return $this->type;
    }

    public function withName($fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function withPath($path): self
    {
        $this->path = $path;
        return $this;
    }

    public function required($required = true): self
    {
        $this->required = $required;
        return $this;
    }

    public function withDefaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function ofTypeString(): self
    {
        return $this->ofType(self::STRING);
    }

    public function ofTypeInt(): self
    {
        return $this->ofType(self::INT);
    }

    public function ofTypeFloat(): self
    {
        return $this->ofType(self::FLOAT);
    }

    public function ofTypeBool(): self
    {
        return $this->ofType(self::BOOL);
    }

    public function ofAnyType(): self
    {
        return $this->ofType(self::ANY);
    }


    protected function ofType($type): self
    {
        if (!in_array($type, [self::STRING, self::INT, self::FLOAT, self::BOOL, self::ANY])) {
            throw new \InvalidArgumentException("Invalid type '$type'");
        }
        $this->type = $type;
        return $this;
    }

    public function validate($value)
    {
        $isEmpty = is_null($value) || (is_array($value) && count($value) == 0);

        if ($isEmpty && $this->isRequired()) {
            throw new InvalidArgumentException("Field '{$this->getFieldName()}' is required");
        }

        if ($this->getType() == JsonFieldDefinition::INT) {
            if (!intval($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be an integer");
            } else {
                $value = intval($value);
            }
        } elseif ($this->getType() == JsonFieldDefinition::FLOAT) {
            if (!is_numeric($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be a number");
            } else {
                $value = floatval($value);
            }
        } elseif ($this->getType() == JsonFieldDefinition::BOOL) {
            if (!is_bool($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be a boolean");
            } else {
                $value = boolval($value);
            }
        } elseif ($this->getType() == JsonFieldDefinition::STRING) {
            if (!is_string($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be a string");
            } else {
                $value = strval($value);
            }
        }

        return $value;

    }

}