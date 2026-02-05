<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use App\Events\TaskCompleted;
use App\Events\TaskOverdue;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    public function getTasksWithFilters(array $filters)
    {
        $cacheKey = 'tasks.' . md5(serialize($filters));

        return Cache::remember($cacheKey, 60, function () use ($filters) {
            return $this->taskRepository->getAll($filters);
        });
    }

    public function createTask(array $data)
    {
        $task = $this->taskRepository->create($data);

        // Можно добавить нотификации
        if ($task->due_date) {
            // Запланировать проверку на просрочку
        }

        return $task;
    }

    public function updateTask(int $id, array $data)
    {
        $task = $this->taskRepository->findById($id);

        // Если задача перешла в статус completed
        if (isset($data['status']) && $data['status'] === 'completed'
            && $task->status !== 'completed') {
            event(new TaskCompleted($task));
        }

        return $this->taskRepository->update($id, $data);
    }

    public function completeTask(int $id)
    {
        $task = $this->taskRepository->findById($id);
        $task->markAsCompleted();

        event(new TaskCompleted($task));

        return $task;
    }

    public function getTaskStatistics()
    {
        return Cache::remember('task_statistics', 300, function () {
            return $this->taskRepository->getStatistics();
        });
    }

    public function checkOverdueTasks(): void
    {
        $tasks = $this->taskRepository->getAll(['status' => 'pending']);

        foreach ($tasks as $task) {
            if ($task->isOverdue()) {
                event(new TaskOverdue($task));
            }
        }
    }
}
