<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            TeamsTableSeeder::class,
            KanbanBoardsTableSeeder::class,
            MeetingsTableSeeder::class,
            TasksTableSeeder::class,
            RiskAlertsTableSeeder::class,
            FollowUpMessagesTableSeeder::class,
        ]);
    }
}
