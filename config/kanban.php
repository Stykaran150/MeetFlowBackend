<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kanban Board Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for Kanban boards.
    |
    */

    'default_columns' => [
        [
            'name' => 'Backlog',
            'color' => '#6B7280',
            'position' => 0,
            'is_done_column' => false,
        ],
        [
            'name' => 'To Do',
            'color' => '#3B82F6',
            'position' => 1,
            'is_done_column' => false,
        ],
        [
            'name' => 'In Progress',
            'color' => '#F59E0B',
            'position' => 2,
            'is_done_column' => false,
        ],
        [
            'name' => 'Review',
            'color' => '#8B5CF6',
            'position' => 3,
            'is_done_column' => false,
        ],
        [
            'name' => 'Done',
            'color' => '#10B981',
            'position' => 4,
            'is_done_column' => true,
        ],
    ],
    
    'default_task_priority' => 'medium',
    
    'deadline_warning_days' => env('KANBAN_DEADLINE_WARNING_DAYS', 3),
];
