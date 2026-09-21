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

    public function record(Model $model, string $event): void
    {
        $modelClass = get_class($model);
        $groupConfig = $this->resolveGroupConfig($modelClass);

        if ($groupConfig === null) {
            return;
        }

        [$oldValues, $newValues] = match ($event) {
            'created' => [null, $model->getAttributes()],
            'updated' => [
                array_intersect_key($model->getOriginal(), $model->getChanges()),
                $model->getChanges(),
            ],
            'deleted' => [$model->getAttributes(), null],
            default   => [null, null],
        };

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
