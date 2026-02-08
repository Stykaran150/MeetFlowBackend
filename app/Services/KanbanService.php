<?php

namespace App\Services;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\Task;
use App\Models\TaskMovement;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KanbanService
{
    /**
     * Create a new kanban board.
     *
     * @param Team $team
     * @param array $data
     * @return KanbanBoard
     */
    public function createBoard(Team $team, array $data): KanbanBoard
    {
        DB::beginTransaction();

        try {
            $board = KanbanBoard::create([
                'team_id' => $team->id,
                'name' => $data['name'] ?? 'New Board',
                'description' => $data['description'] ?? null,
                'is_default' => $data['is_default'] ?? false,
                'created_by' => $data['created_by'],
            ]);

            // Create default columns if not specified
            if (!isset($data['columns'])) {
                $this->createDefaultColumns($board);
            } else {
                foreach ($data['columns'] as $columnData) {
                    KanbanColumn::create([
                        'kanban_board_id' => $board->id,
                        'name' => $columnData['name'],
                        'color' => $columnData['color'] ?? null,
                        'position' => $columnData['position'] ?? 0,
                        'is_done_column' => $columnData['is_done_column'] ?? false,
                    ]);
                }
            }

            DB::commit();
            return $board->load('columns');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get or create default board for team.
     *
     * @param int $teamId
     * @return KanbanBoard
     */
    public function getOrCreateDefaultBoard(int $teamId): KanbanBoard
    {
        $board = KanbanBoard::where('team_id', $teamId)
            ->where('is_default', true)
            ->first();

        if (!$board) {
            $team = Team::findOrFail($teamId);
            $board = $this->createBoard($team, [
                'name' => 'Default Board',
                'is_default' => true,
                'created_by' => $team->owner_id,
            ]);
        }

        return $board->load('columns');
    }

    /**
     * Create default columns for a board.
     *
     * @param KanbanBoard $board
     * @return void
     */
    protected function createDefaultColumns(KanbanBoard $board): void
    {
        $defaultColumns = config('kanban.default_columns');

        foreach ($defaultColumns as $columnData) {
            KanbanColumn::create([
                'kanban_board_id' => $board->id,
                'name' => $columnData['name'],
                'color' => $columnData['color'],
                'position' => $columnData['position'],
                'is_done_column' => $columnData['is_done_column'],
            ]);
        }
    }

    /**
     * Move a task to a different column.
     *
     * @param Task $task
     * @param KanbanColumn $toColumn
     * @param User $movedBy
     * @param string|null $notes
     * @return TaskMovement
     */
    public function moveTask(
        Task $task,
        KanbanColumn $toColumn,
        User $movedBy,
        ?string $notes = null
    ): TaskMovement {
        DB::beginTransaction();

        try {
            $fromColumn = $task->kanbanColumn;

            // Update task position
            $task->update([
                'kanban_column_id' => $toColumn->id,
                'position' => $this->getNextPosition($toColumn),
            ]);

            // If moving to done column, update status
            if ($toColumn->is_done_column) {
                $task->update(['status' => 'completed']);
            } elseif ($toColumn->name === 'In Progress') {
                $task->update(['status' => 'in_progress']);
            }

            // Create movement record
            $movement = TaskMovement::create([
                'task_id' => $task->id,
                'from_column_id' => $fromColumn?->id,
                'to_column_id' => $toColumn->id,
                'moved_by' => $movedBy->id,
                'notes' => $notes,
            ]);

            DB::commit();
            return $movement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get next position for task in column.
     *
     * @param KanbanColumn $column
     * @return int
     */
    protected function getNextPosition(KanbanColumn $column): int
    {
        $lastTask = $column->tasks()->orderBy('position', 'desc')->first();
        return $lastTask ? $lastTask->position + 1 : 0;
    }

    /**
     * Get board with tasks organized by columns.
     *
     * @param KanbanBoard $board
     * @return KanbanBoard
     */
    public function getBoardWithTasks(KanbanBoard $board): KanbanBoard
    {
        return $board->load([
            'columns.tasks' => function ($query) {
                $query->orderBy('position');
            },
            'columns.tasks.assignees',
            'columns.tasks.meeting',
        ]);
    }

    /**
     * Update column positions.
     *
     * @param KanbanBoard $board
     * @param array $columnPositions
     * @return void
     */
    public function updateColumnPositions(KanbanBoard $board, array $columnPositions): void
    {
        DB::beginTransaction();

        try {
            foreach ($columnPositions as $position => $columnId) {
                KanbanColumn::where('id', $columnId)
                    ->where('kanban_board_id', $board->id)
                    ->update(['position' => $position]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
