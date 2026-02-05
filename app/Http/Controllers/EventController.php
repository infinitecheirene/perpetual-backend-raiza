<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Fetch all events
    public function index()
    {
        $events = Event::all();
        return response()->json($events);
    }

    // Create a new event (Admin only)
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'location' => 'required|string|max:255',
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    // In EventController.php
    public function getInvites(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Fetch all events
        $events = Event::with([
            'responses' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }
        ])->get()
            ->map(function ($event) {
                $response = $event->responses->first();

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'category' => $event->category ?? 'General',
                    'image_url' => $event->image_url ?? null,
                    'event_date' => $event->date,
                    'location' => $event->location,
                    'invite_status' => $response?->response ?? 'pending', // pending if no response yet
                    'organizer' => [
                        'name' => 'Admin', // or event->organizer_name if exists
                    ],
                ];
            });

        return response()->json(['data' => $events]);
    }

    // Respond to an event (Accept or Decline)
    public function respond(Request $request, Event $event)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,decline',
        ]);

        $response = EventResponse::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'response' => $validated['action'] === 'pending'
                    ? 'pending'
                    : ($validated['action'] === 'accept' ? 'accepted' : 'declined'),
            ]
        );

        return response()->json([
            'status' => $response->response,
        ]);
    }
}