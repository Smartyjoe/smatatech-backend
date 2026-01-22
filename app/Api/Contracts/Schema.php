<?php

namespace App\Api\Contracts;

/**
 * Schema Builder for API Contracts
 * 
 * Provides fluent interface for defining request/response schemas.
 * These schemas are used for validation and documentation generation.
 */
class Schema
{
    protected array $properties = [];
    protected array $required = [];
    protected string $type = 'object';
    protected ?string $description = null;
    protected mixed $example = null;

    public static function object(?string $description = null): self
    {
        $schema = new self();
        $schema->type = 'object';
        $schema->description = $description;
        return $schema;
    }

    public static function array(?string $itemType = null, ?string $description = null): self
    {
        $schema = new self();
        $schema->type = 'array';
        $schema->description = $description;
        if ($itemType) {
            $schema->properties['items'] = ['type' => $itemType];
        }
        return $schema;
    }

    public function property(string $name, string|array|self $type, bool $required = false, mixed $example = null, ?string $description = null): self
    {
        $prop = [
            'type' => $type instanceof self ? $type->toArray() : $type,
            'required' => $required,
        ];
        
        if ($example !== null) {
            $prop['example'] = $example;
        }
        if ($description !== null) {
            $prop['description'] = $description;
        }

        $this->properties[$name] = $prop;
        
        if ($required) {
            $this->required[] = $name;
        }

        return $this;
    }

    public function required(string ...$fields): self
    {
        $this->required = array_unique(array_merge($this->required, $fields));
        foreach ($fields as $field) {
            if (isset($this->properties[$field])) {
                $this->properties[$field]['required'] = true;
            }
        }
        return $this;
    }

    public function example(mixed $example): self
    {
        $this->example = $example;
        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function toArray(): array
    {
        $result = [
            'type' => $this->type,
        ];

        if ($this->description) {
            $result['description'] = $this->description;
        }

        if ($this->example !== null) {
            $result['example'] = $this->example;
        }

        if ($this->type === 'object' && !empty($this->properties)) {
            $result['properties'] = $this->properties;
            if (!empty($this->required)) {
                $result['required'] = $this->required;
            }
        }

        if ($this->type === 'array' && isset($this->properties['items'])) {
            $result['items'] = $this->properties['items'];
        }

        return $result;
    }

    /**
     * Get Laravel validation rules from schema.
     */
    public function toValidationRules(): array
    {
        $rules = [];
        
        foreach ($this->properties as $name => $prop) {
            $fieldRules = [];
            
            if ($prop['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $type = is_array($prop['type']) ? ($prop['type']['type'] ?? 'string') : $prop['type'];
            
            switch ($type) {
                case 'string':
                    $fieldRules[] = 'string';
                    break;
                case 'integer':
                case 'int':
                    $fieldRules[] = 'integer';
                    break;
                case 'number':
                case 'float':
                case 'double':
                    $fieldRules[] = 'numeric';
                    break;
                case 'boolean':
                case 'bool':
                    $fieldRules[] = 'boolean';
                    break;
                case 'array':
                    $fieldRules[] = 'array';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'uuid':
                    $fieldRules[] = 'uuid';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'datetime':
                    $fieldRules[] = 'date_format:Y-m-d\TH:i:s';
                    break;
            }

            $rules[$name] = implode('|', $fieldRules);
        }

        return $rules;
    }
}

/**
 * Common schema types for reuse
 */
class SchemaTypes
{
    public static function uuid(): array
    {
        return ['type' => 'string', 'format' => 'uuid', 'example' => '550e8400-e29b-41d4-a716-446655440000'];
    }

    public static function email(): array
    {
        return ['type' => 'string', 'format' => 'email', 'example' => 'user@example.com'];
    }

    public static function datetime(): array
    {
        return ['type' => 'string', 'format' => 'date-time', 'example' => '2026-01-20T10:00:00.000000Z'];
    }

    public static function date(): array
    {
        return ['type' => 'string', 'format' => 'date', 'example' => '2026-01-20'];
    }

    public static function url(): array
    {
        return ['type' => 'string', 'format' => 'uri', 'example' => 'https://example.com'];
    }

    public static function pagination(): Schema
    {
        return Schema::object('Pagination metadata')
            ->property('currentPage', 'integer', true, 1)
            ->property('lastPage', 'integer', true, 10)
            ->property('perPage', 'integer', true, 15)
            ->property('total', 'integer', true, 150)
            ->property('from', 'integer', false, 1)
            ->property('to', 'integer', false, 15);
    }

    public static function error(): Schema
    {
        return Schema::object('Error response')
            ->property('code', 'string', true, 'VALIDATION_ERROR', 'Error code from ErrorCode constants')
            ->property('message', 'string', true, 'The given data was invalid.', 'Human-readable error message')
            ->property('details', 'object', false, null, 'Additional error details or validation errors');
    }
}
