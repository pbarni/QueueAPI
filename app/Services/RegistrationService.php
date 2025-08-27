<?php

namespace App\Services;

use App\Exceptions\UserAlreadyRegisteredException;
use App\Exceptions\UserNotRegisteredException;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\RegistrationStatus;
use Exception;

class RegistrationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createRegistration(User $user, Event $event) {
        $current_registration = Registration::where('user_id', $user->id)
                                            ->where('event_id', $event->id)
                                            ->where('status', '!=', RegistrationStatus::CANCELLED)
                                            ->exists();
        if ($current_registration) {
            throw new UserAlreadyRegisteredException();
        }

        $event_members = Registration::where('event_id', $event->id)
                                     ->where('status', RegistrationStatus::REGISTERED)
                                     ->count();

        if ($event_members >= $event->capacity) {
            $already_registered = Registration::where('user_id', $user->id)
                                              ->where('event_id', $event->id)
                                              ->where('status', RegistrationStatus::CANCELLED)
                                              ->first();

            if ($already_registered) {
                $already_registered->status = RegistrationStatus::QUEUED;
                $already_registered->save();
            } else {
                Registration::create([
                        'user_id' => $user->id,
                        'event_id' => $event->id,
                        'status' => RegistrationStatus::QUEUED,
                ]);
            }

            $waitlist_position = Registration::where('event_id', $event->id)
                                             ->where('status', RegistrationStatus::QUEUED)
                                             ->count();

            return [
                'data' => [
                    'message' => 'Successfully enqueued in the waitlist',
                    'waitlist_position' => $waitlist_position
                ],
                'status_code' => 200
            ];
        } else {
            $already_registered = Registration::where('user_id', $user->id)
                                              ->where('event_id', $event->id)
                                              ->where('status', RegistrationStatus::CANCELLED)
                                              ->first();

            if ($already_registered) {
                $already_registered->status = RegistrationStatus::REGISTERED;
                $already_registered->save();

                return [
                    'data' => ['message' => 'Successfully registered'],
                    'status_code' => 201
                ];
            } else {
                Registration::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'status' => RegistrationStatus::REGISTERED,
                ]);

                return [
                    'data' => ['message' => 'Successfully registered'],
                    'status_code' => 201
                ];
            }
        }
    }

    public function deleteRegistration(User $user, Event $event) {
        $current_registration = Registration::where('user_id', $user->id)
                                            ->where('event_id', $event->id)
                                            ->where('status', '!=', RegistrationStatus::CANCELLED)
                                            ->first();

        if ($current_registration) {
            $was_registered = ($current_registration->status === RegistrationStatus::REGISTERED);

            $current_registration->status = RegistrationStatus::CANCELLED;
            $current_registration->save();

            $next_registration = Registration::where('event_id', $event->id)
                                             ->where('status', RegistrationStatus::QUEUED)
                                             ->orderBy('created_at', 'asc')
                                             ->first();

            if ($next_registration && $was_registered) {
                $next_registration->status = RegistrationStatus::REGISTERED;
                $next_registration->save();
            }
        } else {
            throw new UserNotRegisteredException();
        }
    }
}
