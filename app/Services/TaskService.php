<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update a task.
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return bool
     */
    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }

    /**
     * Assign a task to a user.
     *
     * @param Task $task
     * @param User $user
     * @param string $role
     * @return TaskAssignment
     */
    public function assignTask(Task $task, User $user, string $role = 'assignee'): TaskAssignment
    {
        return TaskAssignment::firstOrCreate([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }

    /**
     * Unassign a task from a user.
     *
     * @param Task $task
     * @param User $user
     * @return bool
     */
    public function unassignTask(Task $task, User $user): bool
    {
        return TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Update task priority.
     *
     * @param Task $task
     * @param string $priority
     * @return Task
     */
    public function updatePriority(Task $task, string $priority): Task
    {
        $task->update(['priority' => $priority]);
        return $task->fresh();
    }

    /**
     * Update task deadline.
     *
     * @param Task $task
     * @param \DateTime|null $deadline
     * @return Task
     */
    public function updateDeadline(Task $task, ?\DateTime $deadline): Task
    {
        $task->update(['deadline' => $deadline]);
        return $task->fresh();
    }

    /**
     * Get tasks with filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasks(array $filters = [])
    {
        $query = Task::query();

        if (isset($filters['team_id'])) {
            $query->where('team_id', $filters['team_id']);
        }

        if (isset($filters['team_ids'])) {
            $query->whereIn('team_id', $filters['team_ids']);
        }

        if (isset($filters['meeting_id'])) {
            $query->where('meeting_id', $filters['meeting_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to'])) {
            $query->whereHas('assignees', function ($q) use ($filters) {
                $q->where('users.id', $filters['assigned_to']);
            });
        }

        if (isset($filters['assignment_status'])) {
            if ($filters['assignment_status'] === 'assigned') {
                $query->whereHas('assignees');
            } elseif ($filters['assignment_status'] === 'unassigned') {
                $query->doesntHave('assignees');
            }
        }

        if (isset($filters['overdue'])) {
            $query->where('deadline', '<', now())
                ->where('status', '!=', 'completed');
        }

        return $query->with(['meeting', 'team.users', 'assignees', 'kanbanColumn'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
