<?php

namespace Propello\PackageLearningS;

use Illuminate\Database\Eloquent\Model;

class ChangeDetector
{
    private const EXCLUDED_ATTRIBUTES = ['updated_at'];

    public function getUpdatedValues(Model $model): array
    {
        $changes = array_diff_key($model->getChanges(), array_flip(self::EXCLUDED_ATTRIBUTES));
        $old = array_intersect_key($model->getOriginal(), $changes);

        $filtered = array_filter(
            $changes,
            fn($newVal, $key) => $this->valuesAreDifferent($old[$key] ?? null, $newVal),
            ARRAY_FILTER_USE_BOTH
        );

        return [array_intersect_key($old, $filtered), $filtered];
    }

    public function filterAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip(self::EXCLUDED_ATTRIBUTES));
    }

    private function valuesAreDifferent(mixed $original, mixed $current): bool
    {
        if (is_null($original) !== is_null($current)) {
            return true;
        }

        if (is_null($original)) {
            return false;
        }

        if (is_bool($original)) {
            $original = (int) $original;
        }

        if (is_bool($current)) {
            $current = (int) $current;
        }

        if (is_numeric($original) && is_numeric($current)) {
            return (float) $original !== (float) $current;
        }

        return (string) $original !== (string) $current;
    }
}
