<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-07: users.status = false must actually lock the account out. */
class InactiveUserTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['status' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_active_user_can_still_log_in(): void
    {
        $user = User::factory()->create(['status' => true]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_deactivated_mid_session_is_logged_out_on_next_request(): void
    {
        $user = $this->userWithRole('Admin');
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->forceFill(['status' => false])->save();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_deactivating_a_user_deletes_their_session_rows(): void
    {
        $admin = $this->userWithRole('Admin');
        $target = tap(User::factory()->create(['status' => true]))->assignRole('Employee');
        $other = User::factory()->create(['status' => true]);

        foreach ([$target->id => 'target-sess', $other->id => 'other-sess'] as $userId => $sid) {
            DB::table('sessions')->insert([
                'id' => $sid, 'user_id' => $userId, 'ip_address' => '127.0.0.1',
                'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp,
            ]);
        }

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'status' => '0',
            'roles' => ['Employee'],
        ])->assertRedirect();

        $this->assertFalse($target->fresh()->status);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-sess']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-sess']);
    }
}
