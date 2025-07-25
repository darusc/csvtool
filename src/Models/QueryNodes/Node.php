<?php

namespace Csvtool\Models\QueryNodes;

/**
 * Base class representing the node of the query tree
 */
abstract class Node
{
    protected string $operator;

    /**
     * @param array $row Row to evaluate the condition on
     * @return bool True if the condition is met, false otherwise
     */
    abstract public function evaluate(array $row): bool;
}