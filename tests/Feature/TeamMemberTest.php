<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeamMemberTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test adding a member to a team.
     */
    public function test_owner_can_add_member_to_team()
    {
        // 1. Create Owner and Team
        $owner = User::factory()->create();
        $team = Team::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Avengers'
        ]);
        $team->users()->attach($owner->id, ['role' => 'owner']);

        // 2. Create another user (Member-to-be)
        $memberUser = User::factory()->create([
            'email' => 'tony@stark.com',
            'name' => 'Tony Stark'
        ]);

        // 3. Call API as Owner
        $response = $this->actingAs($owner)
            ->postJson("/api/teams/{$team->id}/members", [
                'email' => 'tony@stark.com',
                'role' => 'member'
            ]);

        // 4. Assert Success
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 5. Verify Database
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $memberUser->id,
            'role' => 'member'
        ]);
    }

    /**
     * Test adding a non-existent user fails.
     */
    public function test_cannot_add_non_existent_user()
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->users()->attach($owner->id, ['role' => 'owner']);

        $response = $this->actingAs($owner)
            ->postJson("/api/teams/{$team->id}/members", [
                'email' => 'ghost@buster.com'
            ]);

        $response->assertStatus(422) // Validation Error
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test non-owner cannot add members.
     */
    public function test_non_owner_cannot_add_member()
    {
        // 1. Create Team
        $owner = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->users()->attach($owner->id, ['role' => 'owner']);

        // 2. Create Random User (Attacker)
        $attacker = User::factory()->create();

        // 3. Create Victim User
        $victim = User::factory()->create(['email' => 'victim@test.com']);

        // 4. Attempt to add victim to team as attacker
        $response = $this->actingAs($attacker)
            ->postJson("/api/teams/{$team->id}/members", [
                'email' => 'victim@test.com'
            ]);

        $response->assertStatus(403); // Forbidden
    }
}
