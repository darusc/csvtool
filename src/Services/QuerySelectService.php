<?php

namespace Csvtool\Services;

use Csvtool\Models\QueryNodes\ComparisonNode;
use Csvtool\Models\QueryNodes\LogicNode;
use Csvtool\Models\QueryNodes\Node;

class QuerySelectService
{
    /**
     * REGEX pattern used to determine the field, operator and value of a comparison node
     */
    public const string CONDITION_OPERATION_PATTERN = "/^\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*(<=|>=|!=|=|<|>)\s*([^,()]+)\s*$/";

    public function __construct(private readonly array $selectedColumns)
    {

    }

    /**
     * Parse given query with the following format
     * and return the root of the condition tree
     *
     * "or(age > 28, and(age = 28, name = 'John Doe'))"
     */
    public function parse(string $query): ?Node
    {
        if(strlen($query) == 0) {
            return null;
        }

        // Attempt to build a comparison node out of the current query
        // If successful than it is a leaf so return it
        $leaf = $this->buildComparisonNode($query);
        if($leaf !== null) {
            return $leaf;
        }

        // Split the query to find the $operator and each of the 2 $operands
        // The $operator will be the root and the $operands will be the 2 children
        $firstBrace = strpos($query, '(');
        $firstEndBrace = strrpos($query, ')');
        $firstComma = strpos($query, ',');

        $operand1 = $this->substrint($query, $firstBrace + 1, $firstComma);
        $left = $this->parse($operand1);

        $operand2 = $this->substrint($query, $firstComma + 1, $firstEndBrace);
        $right = $this->parse($operand2);

        $operation = $this->substrint($query, 0, $firstBrace);
        $root = new LogicNode(strtoupper($operation), $left, $right);

        return $root;
    }

    /**
     * Select only the required columns from given row.
     * Returns a new array containing only needed data
     */
    public function select(array $row): array {
        $result = [];
        foreach ($this->selectedColumns as $column) {
            $result[$column] = $row[$column];
        }
        return $result;
    }

    /**
     * Returns a substring in the interval [start, end)
     */
    private function substrint(string $query, int $start, ?int $end = null): string
    {
        if($end !== null) {
            return trim(substr($query, $start, $end - $start));
        } else {
            return trim(substr($query, $start));
        }
    }

    private function buildComparisonNode(string $operand): ?Node {
        if(preg_match(self::CONDITION_OPERATION_PATTERN, $operand, $matches)) {
            // If the pattern matched the given $operand
            // build the corresponding ComparisonNode and return in
            $field = $matches[1];
            $operator = $matches[2];
            $value = $matches[3];

            return new ComparisonNode($operator, trim($field), trim(trim($value), "'\""));
        }

        return null;
    }
}