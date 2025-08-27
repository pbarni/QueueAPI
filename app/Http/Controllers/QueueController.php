<?php

namespace App\Http\Controllers;

use App\Exceptions\UserAlreadyRegisteredException;
use App\Exceptions\UserNotRegisteredException;
use App\Models\Event;
use App\Services\RegistrationService;
use Exception;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct(protected RegistrationService $registrationService)
    {
    }

    public function index()
    {
        return response()->json(['message' => 'Hello world']);
    }

    public function register(Request $request, Event $event)
    {
        $user = $request->user();

        try {
            $result = $this->registrationService->createRegistration($user, $event);

            return response()->json($result['data'], $result['status_code']);
        } catch (UserAlreadyRegisteredException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unexpected failure at registration'], 500);
        }
    }

    public function unregister(Request $request, Event $event)
    {
        $user = $request->user();

        try {
            $this->registrationService->deleteRegistration($user, $event);

            return response()->json(null, 204);
        } catch (UserNotRegisteredException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unexpected failure at unregistration'], 500);
        }
    }
}
