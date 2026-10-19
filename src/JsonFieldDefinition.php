<?php

namespace ByJG\AnyDataset\Json;

use Closure;
use InvalidArgumentException;

class JsonFieldDefinition
{
    /** @var string $fieldName */
    protected string $fieldName;

    /** @var bool $required */
    protected bool $required = false;

    /** @var mixed $defaultValue */
    protected mixed $defaultValue = null;

    /** @var JsonFieldDefinitionEnum $type */
    protected JsonFieldDefinitionEnum $type;

    protected string|Closure|null $path = null;

    public function __construct(string $fieldName, string|Closure $path, bool $required = false, mixed $defaultValue = null, JsonFieldDefinitionEnum $type = JsonFieldDefinitionEnum::Any)
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

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getPath(): string|Closure|null
    {
        return $this->path;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getType(): JsonFieldDefinitionEnum
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

    public function withDefaultValue(mixed $defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function ofTypeString(): self
    {
        return $this->ofType(JsonFieldDefinitionEnum::String);
    }

    public function ofTypeInt(): self
    {
        return $this->ofType(JsonFieldDefinitionEnum::Int);
    }

    public function ofTypeFloat(): self
    {
        return $this->ofType(JsonFieldDefinitionEnum::Float);
    }

    public function ofTypeBool(): self
    {
        return $this->ofType(JsonFieldDefinitionEnum::Bool);
    }

    public function ofTypeAny(): self
    {
        return $this->ofType(JsonFieldDefinitionEnum::Any);
    }


    protected function ofType(JsonFieldDefinitionEnum $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function validate($value)
    {
        $isEmpty = is_null($value) || (is_array($value) && count($value) == 0);

        if ($isEmpty && $this->isRequired()) {
            throw new InvalidArgumentException("Field '{$this->getFieldName()}' is required");
        }

        // An optional field that is absent (and has no default) has no type to check
        if (is_null($value)) {
            return null;
        }

        if ($this->getType() == JsonFieldDefinitionEnum::Int) {
            // A decimal string may have leading zeros ("012" is 12, never octal), which
            // FILTER_VALIDATE_INT rejects, so they are dropped before the check
            if (is_string($value) && preg_match('/^([+-]?)0*(\d+)$/', trim($value), $matches) === 1) {
                $value = $matches[1] . $matches[2];
            }
            $intValue = is_bool($value) ? false : filter_var($value, FILTER_VALIDATE_INT);
            if ($intValue === false) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be an integer");
            } else {
                $value = $intValue;
            }
        } elseif ($this->getType() == JsonFieldDefinitionEnum::Float) {
            if (!is_numeric($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be a number");
            } else {
                $value = floatval($value);
            }
        } elseif ($this->getType() == JsonFieldDefinitionEnum::Bool) {
            if (!is_bool($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be a boolean");
            } else {
                $value = boolval($value);
            }
        } elseif ($this->getType() == JsonFieldDefinitionEnum::String) {
            if (!is_string($value)) {
                throw new InvalidArgumentException("Field '{$this->getFieldName()}' must be a string");
            } else {
                $value = strval($value);
            }
        }

        return $value;
    }
}