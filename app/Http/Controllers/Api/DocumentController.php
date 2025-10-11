<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\Franchise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents for the authenticated user's franchise.
     */
    public function index(Request $request, $franchise_id = null): JsonResponse
    {
        try {
            $user = auth()->user();

            // Use franchise from route parameter if provided, otherwise use user's franchise
            if ($franchise_id) {
                $franchise = Franchise::findOrFail($franchise_id);
            } else {
                $franchise = $user->franchise;
            }

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user.',
                ], 404);
            }

            // Ensure user has access to this franchise
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to franchise.',
                ], 403);
            }

            $query = $franchise->documents()->latest();

            // Apply filters
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('description', 'like', '%'.$request->search.'%');
                });
            }

            $documents = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $documents,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(StoreDocumentRequest $request, $franchise_id = null): JsonResponse
    {
        try {
            $user = auth()->user();

            // Use franchise from route parameter if provided, otherwise use user's franchise
            if ($franchise_id) {
                $franchise = Franchise::findOrFail($franchise_id);
            } else {
                $franchise = $user->franchise;
            }

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user.',
                ], 404);
            }

            // Ensure user has access to this franchise
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to franchise.',
                ], 403);
            }

            $file = $request->file('file');
            $fileName = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents/'.$franchise->id, $fileName, 'private');

            $document = $franchise->documents()->create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'active',
                'expiry_date' => $request->expiry_date,
                'is_confidential' => $request->boolean('is_confidential', false),
                'metadata' => $request->metadata ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'data' => $document,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified document.
     */
    public function show($franchise_id, $document_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and document from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $document = Document::findOrFail($document_id);

            // Check if user has access to the franchise
            if (! $franchise || ($user->franchise?->id !== $franchise->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                ], 403);
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $document,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download the specified document.
     */
    public function download($franchise_id, $document_id)
    {
        try {
            $user = auth()->user();

            // Resolve franchise and document from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $document = Document::findOrFail($document_id);

            // Check if user has access to the franchise
            if (! $franchise || ($user->franchise?->id !== $franchise->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                ], 403);
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            if (! Storage::disk('local')->exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found.',
                ], 404);
            }

            return Storage::disk('local')->download($document->file_path, $document->file_name);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified document metadata.
     */
    public function update(UpdateDocumentRequest $request, $franchise_id, $document_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and document from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $document = Document::findOrFail($document_id);

            // Check if user has access to the franchise
            if (! $franchise || ($user->franchise?->id !== $franchise->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                ], 403);
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            $document->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully.',
                'data' => $document->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified document.
     */
    public function destroy($franchise_id, $document_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and document from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $document = Document::findOrFail($document_id);

            // Check if user has access to the franchise
            if (! $franchise || ($user->franchise?->id !== $franchise->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.',
                ], 403);
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            // Delete the file from storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            // Delete the document record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a document
     */
    public function approve(Request $request, $franchise_id, $document_id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'comment' => 'required|string|max:500',
            ]);

            $user = auth()->user();

            // Resolve franchise and document from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $document = Document::findOrFail($document_id);

            // Check if user is the franchisor (owner) of the franchise
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only franchisors can approve documents.',
                ], 403);
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            // Update document status to approved and add comment to metadata
            $metadata = $document->metadata ?? [];
            $metadata['approval_comment'] = $validated['comment'];
            $metadata['approved_by'] = $user->id;
            $metadata['approved_at'] = now()->toISOString();
            $metadata['approved_by_name'] = $user->name;

            $document->update([
                'status' => 'approved',
                'metadata' => $metadata,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document approved successfully.',
                'data' => $document->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a document
     */
    public function reject(Request $request, $franchise_id, $document_id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'comment' => 'required|string|max:500',
            ]);

            $user = auth()->user();

            // Resolve franchise and document from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $document = Document::findOrFail($document_id);

            // Check if user is the franchisor (owner) of the franchise
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only franchisors can reject documents.',
                ], 403);
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            // Update document status to rejected and add comment to metadata
            $metadata = $document->metadata ?? [];
            $metadata['rejection_comment'] = $validated['comment'];
            $metadata['rejected_by'] = $user->id;
            $metadata['rejected_at'] = now()->toISOString();
            $metadata['rejected_by_name'] = $user->name;

            $document->update([
                'status' => 'rejected',
                'metadata' => $metadata,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document rejected successfully.',
                'data' => $document->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
