<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MemberProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    /**
     * Admin: List all members with filters
     */
    public function index(Request $request)
    {
        try {
            $query = User::where('role', 'member')
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'address',
                    'fraternity_number',
                    'status',
                    'role',
                    'rejection_reason',
                    'created_at',
                    'updated_at',
                    'email_verified_at'
                ]);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $users = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Show a member by ID
     */
    public function show($id)
    {
        try {
            $user = User::where('role', 'member')
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'address',
                    'fraternity_number',
                    'status',
                    'role',
                    'rejection_reason',
                    'created_at',
                    'updated_at',
                    'email_verified_at'
                ])
                ->find($id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            Log::error('Error fetching user', ['user_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Update member status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,approved,rejected,deactivated',
            'rejection_reason' => 'required_if:status,rejected,deactivated|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($id);
            if (!$user)
                return response()->json(['success' => false, 'message' => 'User not found'], 404);

            $updateData = ['status' => $request->status];
            if (in_array($request->status, ['rejected', 'deactivated']) && $request->rejection_reason) {
                $updateData['rejection_reason'] = $request->rejection_reason;
            } else {
                $updateData['rejection_reason'] = null;
            }

            $user->update($updateData);

            Log::info('User status updated', [
                'user_id' => $user->id,
                'old_status' => $user->getOriginal('status'),
                'new_status' => $request->status,
                'reason' => $request->rejection_reason,
                'updated_by' => auth()->id()
            ]);

            return response()->json(['success' => true, 'message' => 'User status updated', 'data' => $user->fresh()]);
        } catch (\Exception $e) {
            Log::error('Error updating status', ['user_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update status'], 500);
        }
    }

    /**
     * Admin: Get member statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => User::where('role', 'member')->count(),
                'pending' => User::where('role', 'member')->where('status', 'pending')->count(),
                'approved' => User::where('role', 'member')->where('status', 'approved')->count(),
                'rejected' => User::where('role', 'member')->where('status', 'rejected')->count(),
                'deactivated' => User::where('role', 'member')->where('status', 'deactivated')->count(),
            ];

            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Error fetching stats', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch statistics'], 500);
        }
    }

    /**
     * Admin: Export members PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            $query = User::where('role', 'member')
                ->select(['id', 'name', 'email', 'phone_number', 'address', 'fraternity_number', 'status', 'created_at', 'updated_at']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $users = $query->latest()->get();
            $stats = [
                'total' => $users->count(),
                'pending' => $users->where('status', 'pending')->count(),
                'approved' => $users->where('status', 'approved')->count(),
                'rejected' => $users->where('status', 'rejected')->count(),
                'deactivated' => $users->where('status', 'deactivated')->count(),
            ];

            $pdf = Pdf::loadView('pdf.user-report', [
                'users' => $users,
                'stats' => $stats,
                'date' => now()->format('F d, Y'),
                'time' => now()->format('h:i A'),
                'generatedDateTime' => now()->format('F d, Y g:i A'),
            ])->setPaper('a4', 'portrait');

            return $pdf->download('users-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF generation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to generate PDF'], 500);
        }
    }

    /**
     * Authenticated user: Get own basic information (without member profile)
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Load member profile relation
        $user->load('memberProfile');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'membership_id' => $user->membership_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'address' => $user->address,
                    'fraternity_number' => $user->fraternity_number,
                    'status' => $user->status,
                    'role' => $user->role,
                    'rejection_reason' => $user->rejection_reason,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'member_profile' => $user->memberProfile ?? null,
                ],
            ],
        ], 200);
    }

    /**
     * Authenticated user: Update own profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->only([
            'alias',
            'tenure',
            'member_since',
            'projects',
            'positions',
            'achievements',
            'profile_image',
            'juantap_nfc'
        ]);

        // Validate input
        $validator = Validator::make($data, [
            'alias' => 'nullable|string|max:255',
            'tenure' => 'nullable|string|max:255',
            'member_since' => 'nullable|date',
            'projects' => 'nullable|string',
            'positions' => 'nullable|string',
            'achievements' => 'nullable|string',
            'profile_image' => 'nullable|string|max:255',
            'juantap_nfc' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Debugging log to verify input data
        Log::info('Updating MemberProfile with data:', $data);

        // Update or create MemberProfile
        $profile = $user->memberProfile;
        if (!$profile) {
            $profile = $user->memberProfile()->create($data);
            Log::info('Created new MemberProfile:', $profile->toArray());
        } else {
            $profile->update($data);
            Log::info('Updated existing MemberProfile:', $profile->toArray());
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'member_profile' => $profile,
            ],
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $user->memberProfile
        ]);
    }
}
