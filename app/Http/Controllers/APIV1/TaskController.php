<?php

namespace App\Http\Controllers\APIV1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskStoreRequest;

use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskCollection;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class TaskController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $key = "tasks_{$user->id}";


            $tasks =   Cache::remember($key, now()->addHour(), function () use ($request) {
                return Task::where('user_id', $request->user()->id)->latest()->paginate(10);
            });


            return $this->success(new TaskCollection($tasks), 'Tasks retrieved successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 500, $err);
        }
    }



    /**
     * Store a neRwly created resource in storage.
     */
    public function store(TaskStoreRequest $request)
    {
        try {
            $validated = $request->validated();

            $validated['user_id'] = $request->user()->id;

            $task = Task::create($validated);

            return $this->success(new TaskResource($task), 'Task created successfully');
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 500, $err);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        try {
            Gate::authorize('view', $task);


            return $this->success(new TaskResource($task), 'Task retrieved successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is some thing wrong', 403, $err);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest    $request, Task $task): JsonResponse
    {

        try {

            $validated = $request->validated();

            Gate::authorize('update', $task);

            $task->update($validated);
            return $this->success(new TaskResource($task), 'Task updated successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 403, $err);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {

        try {
            if (!$task) {
                throw new \Exception('There is no such task');
            }
            Gate::authorize('delete', $task);
            $task->delete();
            return $this->success(null, 'Task deleted successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 403);
        }
    }
    public function restore($id, Request $request)
    {
        try {
            $task = Task::onlyTrashed()->findOrFail($id);
            Gate::authorize('restore', $task);
            $task->restore();
            return $this->success(new TaskResource($task), 'Task restore successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something went wrong', 403, $err);
        }
    }

    public function forceDelete($id)
    {
        try {
            $task = Task::onlyTrashed()->findOrFail($id);
            Gate::authorize('forceDelete', $task);
            $task->forceDelete();
            return $this->success(new TaskResource($task), 'Task deleted permanently', 200);
        } catch (\Throwable $err) {

            return $this->error('There is something went wrong', 500, $err);
        }
    }
    public function filter(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,completed,cancel',
        ]);
        try {

            $tasks = Auth::user()->tasks()->where('status', $validated['status'])->latest()->paginate(10);
            return $this->success(new TaskCollection($tasks), 'Tasks filtered successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 500, $err);
        }
    }

    public function updateStatus(Task $task, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,completed,cancel',
        ]);
        try {

            Gate::authorize('update', $task);
            $task->update($validated);
            return $this->success(new TaskResource($task), 'Task status updated successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 500, $err);
        }
    }
    public function getTrashed(Request $request)
    {
        try {

            $tasks = Auth::user()->tasks()->onlyTrashed()->latest()->paginate(10);

            return $this->success(new TaskCollection($tasks), 'Task fetch successfully', 200);
        } catch (\Throwable $err) {
            return $this->error('There is something wrong', 500, $err);
        }
    }

    public function summary(Request $request)
    {
        try {
            $user = Auth::user();

            $summary = $user->tasks()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            // Ensure all statuses are present, even if 0
            $allStatuses = ['new', 'in_progress', 'completed', 'cancel'];
            $finalSummary = [];

            foreach ($allStatuses as $status) {
                $finalSummary[$status] = $summary[$status] ?? 0;
            }

            return $this->success($finalSummary, 'Task summary fetched successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to load task summary', 500, $e);
        }
    }
}
