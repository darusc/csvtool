<?php

namespace Csvtool\Models\QueryNodes;

/**
 * Node for logical operations
 * AND, OR, NOT
 */
class LogicNode extends Node
{
    public function __construct(
        string $operator,
        private readonly Node $left,
        private readonly ?Node $right = null)
    {
        $this->operator = $operator;
    }

    public function evaluate(array $row): bool
    {
        return match ($this->operator) {
            'AND' => $this->left->evaluate($row) && $this->right->evaluate($row),
            'OR' => $this->left->evaluate($row) || $this->right->evaluate($row),
            'NOT' => !$this->left->evaluate($row)
        };
    }
}