<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    /**
     * Display a listing of news
     */
    public function index(Request $request)
    {
        try {
            $query = News::with('author:id,name,email');

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by category
            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate
            $perPage = $request->get('per_page', 15);
            $news = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            Log::error('News index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch news',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created news
     */
    public function store(Request $request)
    {
        try {
            Log::info('=== NEWS STORE REQUEST START ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('All request data:', $request->all());
            Log::info('Has image file: ' . ($request->hasFile('image') ? 'YES' : 'NO'));
            
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                Log::info('Image file details:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                    'valid' => $file->isValid()
                ]);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category' => 'required|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
                'status' => 'nullable|in:draft,published,archived',
                'published_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $data['author_id'] = auth()->id();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                Log::info('Processing image upload');
                
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                
                // Move to public/images/news directory
                $destinationPath = public_path('images/news');
                
                // Create directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                    Log::info('Created directory: ' . $destinationPath);
                }
                
                $image->move($destinationPath, $imageName);
                
                // CRITICAL FIX: Use image_url to match database column
                $data['image_url'] = 'images/news/' . $imageName;
                
                Log::info('Image saved successfully:', [
                    'filename' => $imageName,
                    'path' => $data['image_url'],
                    'full_path' => $destinationPath . '/' . $imageName
                ]);
            }

            // Set published_at if status is published
            if (isset($data['status']) && $data['status'] === 'published' && !isset($data['published_at'])) {
                $data['published_at'] = now();
            }

            Log::info('Creating news with data:', $data);

            $news = News::create($data);
            $news->load('author:id,name,email');

            Log::info('News created successfully with ID: ' . $news->id);
            Log::info('=== NEWS STORE REQUEST END ===');

            return response()->json([
                'success' => true,
                'message' => 'News created successfully',
                'data' => $news,
            ], 201);
        } catch (\Exception $e) {
            Log::error('=== NEWS STORE ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create news',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Display the specified news
     */
    public function show($id)
    {
        try {
            $news = News::with('author:id,name,email')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'News not found',
            ], 404);
        }
    }

    /**
     * Update the specified news
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('=== NEWS UPDATE REQUEST START ===');
            Log::info('News ID: ' . $id);
            Log::info('Request method: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('_method parameter: ' . $request->input('_method'));
            Log::info('All request data:', $request->except('image'));
            Log::info('Has image file: ' . ($request->hasFile('image') ? 'YES' : 'NO'));

            $news = News::findOrFail($id);
            
            Log::info('Found news:', [
                'id' => $news->id,
                'title' => $news->title,
                'current_image_url' => $news->image_url
            ]);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'category' => 'sometimes|required|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'status' => 'nullable|in:draft,published,archived',
                'published_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            
            Log::info('Validated data:', $data);

            // Handle image upload
            if ($request->hasFile('image')) {
                Log::info('Processing new image upload');
                
                // Delete old image - CRITICAL FIX: Use image_url column
                if ($news->image_url && file_exists(public_path($news->image_url))) {
                    $deleted = unlink(public_path($news->image_url));
                    Log::info('Old image deletion:', [
                        'path' => $news->image_url,
                        'deleted' => $deleted ? 'SUCCESS' : 'FAILED'
                    ]);
                } else {
                    Log::info('No old image to delete or file not found:', [
                        'image_url' => $news->image_url,
                        'path_exists' => $news->image_url ? file_exists(public_path($news->image_url)) : false
                    ]);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                
                // Move to public/images/news directory
                $destinationPath = public_path('images/news');
                
                // Create directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                    Log::info('Created directory: ' . $destinationPath);
                }
                
                $image->move($destinationPath, $imageName);
                
                // CRITICAL FIX: Use image_url to match database column
                $data['image_url'] = 'images/news/' . $imageName;
                
                Log::info('New image saved:', [
                    'filename' => $imageName,
                    'path' => $data['image_url'],
                    'full_path' => $destinationPath . '/' . $imageName
                ]);
            }

            // Update published_at if changing to published status
            if (isset($data['status']) && $data['status'] === 'published' && !$news->published_at) {
                $data['published_at'] = $data['published_at'] ?? now();
                Log::info('Setting published_at: ' . $data['published_at']);
            }

            Log::info('Updating news with data:', $data);

            $news->update($data);
            $news->load('author:id,name,email');

            Log::info('News updated successfully:', [
                'id' => $news->id,
                'image_url' => $news->image_url
            ]);
            Log::info('=== NEWS UPDATE REQUEST END ===');

            return response()->json([
                'success' => true,
                'message' => 'News updated successfully',
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            Log::error('=== NEWS UPDATE ERROR ===');
            Log::error('Error message: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update news',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Remove the specified news
     */
    public function destroy($id)
    {
        try {
            Log::info('=== NEWS DELETE REQUEST ===');
            Log::info('Deleting news ID: ' . $id);
            
            $news = News::findOrFail($id);

            // Delete image if exists - CRITICAL FIX: Use image_url column
            if ($news->image_url && file_exists(public_path($news->image_url))) {
                $deleted = unlink(public_path($news->image_url));
                Log::info('Image deletion:', [
                    'path' => $news->image_url,
                    'deleted' => $deleted ? 'SUCCESS' : 'FAILED'
                ]);
            }

            $news->delete();

            Log::info('News deleted successfully: ' . $id);

            return response()->json([
                'success' => true,
                'message' => 'News deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('News delete error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete news',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get published news for public view
     */
    public function published(Request $request)
    {
        try {
            $query = News::published()->with('author:id,name');

            // Filter by category
            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $query->orderBy('published_at', 'desc');

            $perPage = $request->get('per_page', 12);
            $news = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $news,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch published news',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}