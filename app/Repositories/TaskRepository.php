<?php

namespace App\Repositories;

use App\Models\Task;
use App\Interfaces\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        private Task $model
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('user')->latest();

        // Применяем фильтры
        foreach ($filters as $key => $value) {
            if ($value && in_array($key, ['status', 'priority', 'user_id'])) {
                $query->where($key, $value);
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate(10)->withQueryString();
    }

    public function findById(int $id): ?Task
    {
        return $this->model->with('user')->findOrFail($id);
    }

    public function create(array $data): Task
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $task = $this->findById($id);
        return $task->update($data);
    }

    public function delete(int $id): bool
    {
        $task = $this->findById($id);
        return $task->delete();
    }

    public function restore(int $id): bool
    {
        return $this->model->withTrashed()->where('id', $id)->restore();
    }

    public function forceDelete(int $id): bool
    {
        $task = $this->model->withTrashed()->findOrFail($id);
        return $task->forceDelete();
    }

    public function getUserTasks(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function getStatistics(): array
    {
        return [
            'total'     => $this->model->count(),
            'completed' => $this->model->completed()->count(),
            'pending'   => $this->model->pending()->count(),
            'overdue'   => $this->model->pending()
                ->where('due_date', '<', now())
                ->count(),
        ];
    }
}
