<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Task $task): bool
    {
        return $user->id == $task->user_id;
    }
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
    public function restore(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}
