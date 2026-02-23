<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailList;
use App\Models\User;

class ManageEmailListAction
{
    public function create(int $tenantId, int $userId, array $data): EmailList
    {
        return EmailList::create([
            'tenant_id' => $tenantId,
            'created_by' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? EmailList::TYPE_STATIC,
            'filters' => $data['filters'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    public function update(EmailList $list, array $data): EmailList
    {
        $list->update([
            'name' => $data['name'] ?? $list->name,
            'description' => $data['description'] ?? $list->description,
            'type' => $data['type'] ?? $list->type,
            'filters' => $data['filters'] ?? $list->filters,
            'is_active' => $data['is_active'] ?? $list->is_active,
        ]);

        return $list->fresh();
    }

    public function addSubscribers(EmailList $list, array $subscribers): int
    {
        $added = 0;

        foreach ($subscribers as $subscriber) {
            if (isset($subscriber['user_id'])) {
                $user = User::find($subscriber['user_id']);
                if ($user && !$list->isUserSubscribed($user->id)) {
                    $list->addSubscriber($user, $subscriber['source'] ?? 'import');
                    $added++;
                }
            } elseif (isset($subscriber['email'])) {
                $existing = $list->subscribers()
                    ->where('email', $subscriber['email'])
                    ->first();

                if (!$existing) {
                    $list->addSubscriberByEmail(
                        $subscriber['email'],
                        $subscriber['name'] ?? null,
                        $subscriber['source'] ?? 'import'
                    );
                    $added++;
                }
            }
        }

        return $added;
    }

    public function importFromCsv(EmailList $list, string $csvContent): array
    {
        $lines = explode("\n", trim($csvContent));
        $header = str_getcsv(array_shift($lines));

        $emailIndex = array_search('email', array_map('strtolower', $header));
        $nameIndex = array_search('name', array_map('strtolower', $header));

        if ($emailIndex === false) {
            throw new \Exception('El archivo CSV debe contener una columna "email"');
        }

        $subscribers = [];
        $errors = [];

        foreach ($lines as $lineNumber => $line) {
            if (empty(trim($line))) {
                continue;
            }

            $data = str_getcsv($line);
            $email = $data[$emailIndex] ?? null;

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = [
                    'line' => $lineNumber + 2,
                    'email' => $email,
                    'error' => 'Email inválido',
                ];
                continue;
            }

            $subscribers[] = [
                'email' => $email,
                'name' => $nameIndex !== false ? ($data[$nameIndex] ?? null) : null,
                'source' => 'csv_import',
            ];
        }

        $added = $this->addSubscribers($list, $subscribers);

        return [
            'added' => $added,
            'total' => count($subscribers),
            'errors' => $errors,
        ];
    }

    public function syncDynamicList(EmailList $list): int
    {
        if ($list->type !== EmailList::TYPE_DYNAMIC) {
            throw new \Exception('Esta operación solo aplica a listas dinámicas');
        }

        $filters = $list->filters;

        if (empty($filters)) {
            return 0;
        }

        // Build query based on filters
        $query = User::query();

        if (!empty($filters['roles'])) {
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $filters['roles']));
        }

        if (!empty($filters['created_after'])) {
            $query->where('created_at', '>=', $filters['created_after']);
        }

        if (!empty($filters['created_before'])) {
            $query->where('created_at', '<=', $filters['created_before']);
        }

        if (!empty($filters['has_subscription'])) {
            $query->whereHas('subscriptions', fn($q) => $q->where('status', 'active'));
        }

        if (!empty($filters['has_enrollment'])) {
            $query->whereHas('enrollments');
        }

        $users = $query->get();

        return $this->addSubscribers($list, $users->map(fn($u) => [
            'user_id' => $u->id,
            'source' => 'dynamic_sync',
        ])->toArray());
    }
}
