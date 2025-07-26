<?php

namespace Csvtool\Models\QueryNodes;

/**
 * Node for comparison operations
 * <, <= , >, >=, =, !=
 */
class ComparisonNode extends Node
{
    public function __construct(
        string $operator,
        private readonly string $field,
        private readonly string $value,
    )
    {
        $this->operator = $operator;
    }

    public function evaluate(array $row): bool
    {
        $data = $row[$this->field];
        return match ($this->operator) {
            '<' => $data < $this->value,
            '<=' => $data <= $this->value,
            '>' => $data > $this->value,
            '>=' => $data >= $this->value,
            '=' => $data == $this->value,
            '!=' => $data != $this->value,
        };
    }
}