<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['unit', 'franchisee']);

        // Apply filters
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->has('sentiment')) {
            $query->where('sentiment', $request->sentiment);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'review_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => 'Reviews retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'sentiment' => 'required|in:positive,neutral,negative',
            'status' => 'required|in:published,draft,archived',
            'internal_notes' => 'nullable|string|max:500',
            'review_source' => 'required|in:in_person,phone,email,social_media,other',
            'verified_purchase' => 'required|boolean',
            'review_date' => 'required|date',
        ]);

        // Set the franchisee_id to the current authenticated user
        $validated['franchisee_id'] = auth()->id();

        $review = Review::create($validated);

        return response()->json([
            'success' => true,
            'data' => $review->load(['unit', 'franchisee']),
            'message' => 'Customer review added successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review): JsonResponse
    {
        $review->load(['unit', 'franchisee']);

        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Review retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'sentiment' => 'sometimes|in:positive,neutral,negative',
            'status' => 'sometimes|in:published,draft,archived',
            'internal_notes' => 'nullable|string|max:500',
            'review_source' => 'sometimes|in:in_person,phone,email,social_media,other',
            'verified_purchase' => 'sometimes|boolean',
            'review_date' => 'sometimes|date',
        ]);

        $review->update($validated);

        return response()->json([
            'success' => true,
            'data' => $review->load(['unit', 'franchisee']),
            'message' => 'Review updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Publish a review (make it visible)
     */
    public function publish(Review $review): JsonResponse
    {
        $review->update(['status' => 'published']);

        return response()->json([
            'success' => true,
            'data' => $review->load(['unit', 'franchisee']),
            'message' => 'Review published successfully',
        ]);
    }

    /**
     * Archive a review (hide it from public view)
     */
    public function archive(Review $review): JsonResponse
    {
        $review->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'data' => $review->load(['unit', 'franchisee']),
            'message' => 'Review archived successfully',
        ]);
    }

    /**
     * Update internal notes for a review
     */
    public function updateNotes(Request $request, Review $review): JsonResponse
    {
        $validated = $request->validate([
            'internal_notes' => 'required|string|max:500',
        ]);

        $review->update(['internal_notes' => $validated['internal_notes']]);

        return response()->json([
            'success' => true,
            'data' => $review->load(['unit', 'franchisee']),
            'message' => 'Internal notes updated successfully',
        ]);
    }

    /**
     * Get review statistics for a unit
     */
    public function statistics(Unit $unit): JsonResponse
    {
        $reviews = $unit->reviews();

        $stats = [
            'total_reviews' => $reviews->count(),
            'published_reviews' => $reviews->published()->count(),
            'draft_reviews' => $reviews->draft()->count(),
            'archived_reviews' => $reviews->archived()->count(),
            'average_rating' => $reviews->published()->avg('rating') ?: 0,
            'rating_distribution' => [
                1 => $reviews->published()->where('rating', 1)->count(),
                2 => $reviews->published()->where('rating', 2)->count(),
                3 => $reviews->published()->where('rating', 3)->count(),
                4 => $reviews->published()->where('rating', 4)->count(),
                5 => $reviews->published()->where('rating', 5)->count(),
            ],
            'sentiment_distribution' => [
                'positive' => $reviews->published()->where('sentiment', 'positive')->count(),
                'neutral' => $reviews->published()->where('sentiment', 'neutral')->count(),
                'negative' => $reviews->published()->where('sentiment', 'negative')->count(),
            ],
            'source_distribution' => [
                'in_person' => $reviews->published()->where('review_source', 'in_person')->count(),
                'phone' => $reviews->published()->where('review_source', 'phone')->count(),
                'email' => $reviews->published()->where('review_source', 'email')->count(),
                'social_media' => $reviews->published()->where('review_source', 'social_media')->count(),
                'other' => $reviews->published()->where('review_source', 'other')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Review statistics retrieved successfully',
        ]);
    }
}
