<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\RegistrationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function a_known_user_can_register_for_the_open_event(): void
    {
        $user = User::find(1);
        $openEvent = Event::find(1);

        $response = $this->actingAs($user)
                         ->postJson("/api/{$openEvent->id}/register");

        $response->assertStatus(201);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $user->id,
            'event_id' => $openEvent->id,
            'status' => 'registered',
        ]);
    }

    #[Test]
    public function a_known_user_can_fill_the_last_spot_on_the_limited_event(): void
    {
        $user1 = User::find(1);
        $user2 = User::find(2);
        $limitedEvent = Event::find(2);

        $this->actingAs($user2)->postJson("/api/{$limitedEvent->id}/register");

        $response = $this->actingAs($user1)
                         ->postJson("/api/{$limitedEvent->id}/register");

        $response->assertStatus(201);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $user1->id,
            'event_id' => $limitedEvent->id,
            'status' => 'registered',
        ]);
    }

    #[Test]
    public function a_third_user_is_placed_on_the_waitlist_for_the_now_full_limited_event(): void
    {
        $user1 = User::find(1);
        $user2 = User::find(2);
        $user3 = User::find(3);
        $limitedEvent = Event::find(2);

        $this->actingAs($user1)->postJson("/api/{$limitedEvent->id}/register");
        $this->actingAs($user2)->postJson("/api/{$limitedEvent->id}/register");

        $response = $this->actingAs($user3)
                         ->postJson("/api/{$limitedEvent->id}/register");

        $response->assertStatus(200);
        $response->assertJson(['waitlist_position' => 1]);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $user3->id,
            'event_id' => $limitedEvent->id,
            'status' => 'queued',
        ]);
    }

    #[Test]
    public function cancelling_a_registration_promotes_a_user_from_the_waitlist(): void
    {
        $user1 = User::find(1);
        $user2 = User::find(2);
        $limitedEvent = Event::find(3);

        $this->actingAs($user1)->postJson("/api/{$limitedEvent->id}/register");

        $this->actingAs($user2)->postJson("/api/{$limitedEvent->id}/register");

        $response = $this->actingAs($user1)
                         ->deleteJson("/api/{$limitedEvent->id}/register");

        $response->assertStatus(204);

        $this->assertDatabaseHas('registrations', [
            'user_id' => $user2->id,
            'event_id' => $limitedEvent->id,
            'status' => 'registered',
        ]);

        $this->assertDatabaseHas('registrations', [
            'user_id' => $user1->id,
            'event_id' => $limitedEvent->id,
            'status' => 'cancelled',
        ]);
    }

    #[Test]
    public function a_user_who_previously_cancelled_can_re_register_for_an_event_with_space(): void
    {
        $userToReRegister = User::find(1);
        $eventWithSpace = Event::find(1);

        \App\Models\Registration::create([
            'user_id' => $userToReRegister->id,
            'event_id' => $eventWithSpace->id,
            'status' => RegistrationStatus::CANCELLED,
        ]);

        $this->assertDatabaseCount('registrations', 1);

        $response = $this->actingAs($userToReRegister)
                         ->postJson("/api/{$eventWithSpace->id}/register");

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Successfully registered']);

        $this->assertDatabaseHas('registrations', [
            'user_id' => $userToReRegister->id,
            'event_id' => $eventWithSpace->id,
            'status' => 'registered',
        ]);

        $this->assertDatabaseCount('registrations', 1);
    }

    #[Test]
    public function a_user_who_previously_cancelled_is_placed_on_the_waitlist_for_a_full_event(): void
    {
        $userToReRegister = User::find(1);
        $otherUser = User::find(2);
        $fullEvent = Event::find(3);

        \App\Models\Registration::create([
            'user_id' => $otherUser->id,
            'event_id' => $fullEvent->id,
            'status' => RegistrationStatus::REGISTERED,
        ]);

        \App\Models\Registration::create([
            'user_id' => $userToReRegister->id,
            'event_id' => $fullEvent->id,
            'status' => RegistrationStatus::CANCELLED,
        ]);

        $this->assertDatabaseCount('registrations', 2);

        $response = $this->actingAs($userToReRegister)
                         ->postJson("/api/{$fullEvent->id}/register");

        $response->assertStatus(200);
        $response->assertJson(['waitlist_position' => 1]);

        $this->assertDatabaseHas('registrations', [
            'user_id' => $userToReRegister->id,
            'event_id' => $fullEvent->id,
            'status' => 'queued',
        ]);

        $this->assertDatabaseCount('registrations', 2);
    }
}
