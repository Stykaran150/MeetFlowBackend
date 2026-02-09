<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamsTableSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'demo@meetflow.ai')->first();
        
        if (!$owner) return;

        $team = Team::firstOrCreate(
            ['name' => 'MeetFlow Product Launch'],
            [
                'owner_id' => $owner->id,
                'description' => 'Core team responsible for the Q1 product launch of MeetFlow AI.',
            ]
        );

        // Attach all existing users to this team
        $users = User::all();
        foreach ($users as $user) {
            $role = ($user->id === $owner->id) ? 'owner' : 'member';
            if (!$team->users()->where('user_id', $user->id)->exists()) {
                $team->users()->attach($user->id, ['role' => $role]);
            }
        }
    }
}
