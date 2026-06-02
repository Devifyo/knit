<?php

declare(strict_types=1);

namespace App\Modules\Automation\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Evaluates a condition tree against a model. Tree shape:
 *
 *   { "operator": "and"|"or", "rules": [ {"field": "...", "op": "...", "value": ...}, ... ] }
 *
 * Supported ops: equals, not_equals, contains, gt, lt, gte, lte, is_empty, is_not_empty.
 * An empty/absent tree evaluates to true.
 */
class ConditionEvaluator
{
    /**
     * @param  array<string, mixed>|null  $tree
     */
    public function evaluate(?array $tree, Model $subject): bool
    {
        if (empty($tree) || empty($tree['rules'])) {
            return true;
        }

        $operator = strtolower((string) ($tree['operator'] ?? 'and'));
        $results = array_map(fn (array $rule) => $this->matchRule($rule, $subject), $tree['rules']);

        return $operator === 'or' ? in_array(true, $results, true) : ! in_array(false, $results, true);
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    protected function matchRule(array $rule, Model $subject): bool
    {
        $actual = data_get($subject, $rule['field'] ?? '');
        $expected = $rule['value'] ?? null;

        return match ($rule['op'] ?? 'equals') {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'contains' => is_string($actual) && str_contains($actual, (string) $expected),
            'gt' => $actual > $expected,
            'lt' => $actual < $expected,
            'gte' => $actual >= $expected,
            'lte' => $actual <= $expected,
            'is_empty' => blank($actual),
            'is_not_empty' => filled($actual),
            default => false,
        };
    }
}
