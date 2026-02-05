<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Fetch all events (public)
    public function index()
    {
        $events = Event::with(['responses' => function ($query) {
            $query->select('id', 'event_id', 'user_id', 'response', 'created_at');
        }, 'responses.user' => function ($query) {
            $query->select('id', 'name', 'email');
        }])->get();
        
        return response()->json($events);
    }

    // Get events for admin with detailed statistics
    public function adminIndex(Request $request)
    {
        $query = Event::with(['responses' => function ($query) {
            $query->select('id', 'event_id', 'user_id', 'response', 'created_at');
        }, 'responses.user' => function ($query) {
            $query->select('id', 'name', 'email');
        }]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $events = $query->paginate(15);

        // Add statistics to each event
        $events->getCollection()->transform(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'date' => $event->date,
                'time' => $event->time,
                'location' => $event->location,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
                'stats' => [
                    'total_invites' => $event->responses->count(),
                    'accepted' => $event->responses->where('response', 'accepted')->count(),
                    'declined' => $event->responses->where('response', 'declined')->count(),
                    'pending' => $event->responses->where('response', 'pending')->count(),
                ],
                'responses' => $event->responses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'user_id' => $response->user_id,
                        'user_name' => $response->user->name,
                        'user_email' => $response->user->email,
                        'response' => $response->response,
                        'responded_at' => $response->created_at,
                    ];
                }),
            ];
        });

        return response()->json($events);
    }

    // Create a new event (Admin only)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'location' => 'required|string|max:255',
        ]);

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    // Update an event
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required|date_format:H:i',
            'location' => 'sometimes|required|string|max:255',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }

    // Delete an event
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }

    // Get detailed view of a single event with responses
    public function show(Event $event)
    {
        $event->load(['responses' => function ($query) {
            $query->select('id', 'event_id', 'user_id', 'response', 'created_at');
        }, 'responses.user' => function ($query) {
            $query->select('id', 'name', 'email');
        }]);

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'date' => $event->date,
            'time' => $event->time,
            'location' => $event->location,
            'created_at' => $event->created_at,
            'updated_at' => $event->updated_at,
            'stats' => [
                'total_invites' => $event->responses->count(),
                'accepted' => $event->responses->where('response', 'accepted')->count(),
                'declined' => $event->responses->where('response', 'declined')->count(),
                'pending' => $event->responses->where('response', 'pending')->count(),
            ],
            'responses' => $event->responses->map(function ($response) {
                return [
                    'id' => $response->id,
                    'user_id' => $response->user_id,
                    'user_name' => $response->user->name,
                    'user_email' => $response->user->email,
                    'response' => $response->response,
                    'responded_at' => $response->created_at,
                ];
            }),
        ]);
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