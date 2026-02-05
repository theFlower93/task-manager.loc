<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskCollection;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Task::class, 'task');
    }

    public function index(): TaskCollection
    {
        $filters = request()->only(['status', 'priority', 'search']);
        $tasks = $this->taskService->getTasksWithFilters($filters);

        return new TaskCollection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json([
            'message' => 'Task created successfully',
            'data' => new TaskResource($task)
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): TaskResource
    {
        $task = $this->taskService->getTaskById($id);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $this->taskService->updateTask($id, $request->validated());

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => new TaskResource($this->taskService->getTaskById($id))
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->taskService->deleteTask($id);

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    public function complete(int $id): JsonResponse
    {
        $task = $this->taskService->completeTask($id);

        return response()->json([
            'message' => 'Task marked as completed',
            'data' => new TaskResource($task)
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = $this->taskService->getTaskStatistics();

        return response()->json([
            'data' => $stats
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $this->taskService->restoreTask($id);

        return response()->json([
            'message' => 'Task restored successfully'
        ]);
    }
}
