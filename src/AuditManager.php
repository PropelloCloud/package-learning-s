<?php

namespace Propello\PackageLearningS;

use Illuminate\Database\Eloquent\Model;
use Propello\PackageLearningS\Models\AuditLogEntry;

class AuditManager
{
    private array $pending = [];

    public function __construct(
        private array $config,
        private readonly ChangeDetector $detector = new ChangeDetector(),
    ) {}

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
            'created' => [null, $this->detector->filterAttributes($model->getAttributes())],
            'updated' => $this->detector->getUpdatedValues($model),
            'deleted' => [$this->detector->filterAttributes($model->getAttributes()), null],
            default => [null, null],
        };

        if (empty($oldValues) && empty($newValues)) {
            return;
        }

        $groupId = $groupConfig['group_id_column']
            ? (string) $model->getAttribute($groupConfig['group_id_column'])
            : null;

        $key = $groupConfig['group_name'] . ':' . ($groupId ?? '') . ':' . $event;

        if (isset($this->pending[$key])) {
            $this->pending[$key]['old_values'] = array_merge($this->pending[$key]['old_values'], $oldValues ?? []);
            $this->pending[$key]['new_values'] = array_merge($this->pending[$key]['new_values'], $newValues ?? []);
        } else {
            $this->pending[$key] = [
                'group_name' => $groupConfig['group_name'],
                'group_id' => $groupId,
                'event' => $event,
                'old_values' => $oldValues ?? [],
                'new_values' => $newValues ?? [],
                'user_id' => auth()->id(),
            ];
        }
    }

    public function saveBufferedLog(): void
    {
        foreach ($this->pending as $entry) {
            AuditLogEntry::create($entry);
        }

        $this->pending = [];
    }
}
