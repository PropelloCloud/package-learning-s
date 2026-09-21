<?php

namespace Propello\PackageLearningS;

use Illuminate\Database\Eloquent\Model;
use Propello\PackageLearningS\Models\AuditLogEntry;

class AuditManager
{
    public function __construct(protected array $config) {}

    public function resolveGroupConfig(string $modelClass): ?array
    {
        foreach ($this->config as $groupName => $groupConfig) {
            foreach ($groupConfig['models'] as $key => $value) {
                $class = is_int($key) ? $value : $key;

                if ($class === $modelClass) {
                    $groupIdColumn = is_array($value) && isset($value['group_id_column'])
                        ? $value['group_id_column']
                        : $groupConfig['group_id_column'] ?? null;

                    return [
                        'group_name' => $groupName,
                        'group_id_column' => $groupIdColumn,
                    ];
                }
            }
        }

        return null;
    }

    private function filterAttributes(array $attributes, array $exclude): array
    {
        return array_diff_key($attributes, array_flip($exclude));
    }

    public function record(Model $model, string $event): void
    {
        $modelClass = get_class($model);
        $groupConfig = $this->resolveGroupConfig($modelClass);

        if ($groupConfig === null) {
            return;
        }

        $exclude = ['updated_at'];

        [$oldValues, $newValues] = match ($event) {
            'created' => [null, $this->filterAttributes($model->getAttributes(), $exclude)],
            'updated' => (function () use ($model, $exclude) {
                $changes = array_diff_key($model->getChanges(), array_flip($exclude));
                $old = array_intersect_key($model->getRawOriginal(), $changes);
                return [$old, $changes];
            })(),
            'deleted' => [$this->filterAttributes($model->getAttributes(), $exclude), null],
            default   => [null, null],
        };

        if (empty($oldValues) && empty($newValues)) {
            return;
        }

        AuditLogEntry::create([
            'group_name' => $groupConfig['group_name'],
            'group_id' => $groupConfig['group_id_column']
                ? $model->getAttribute($groupConfig['group_id_column'])
                : null,
            'auditable_type' => $modelClass,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => auth()->id(),
        ]);
    }
}
