<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\Franchise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends BaseResourceController
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
                return $this->notFoundResponse('No franchise found for this user.');
            }

            // Ensure user has access to this franchise
            if ($franchise->franchisor_id !== $user->id) {
                return $this->forbiddenResponse('Unauthorized access to franchise.');
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

            return $this->successResponse($documents);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve documents.', $e->getMessage(), 500);
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
                return $this->notFoundResponse('No franchise found for this user.');
            }

            // Ensure user has access to this franchise
            if ($franchise->franchisor_id !== $user->id) {
                return $this->forbiddenResponse('Unauthorized access to franchise.');
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

            return $this->successResponse($document, 'Document uploaded successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload document.', $e->getMessage(), 500);
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
                return $this->forbiddenResponse('Access denied.');
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return $this->notFoundResponse('Document not found.');
            }

            return $this->successResponse($document);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve document.', $e->getMessage(), 500);
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
                return $this->forbiddenResponse('Access denied.');
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return $this->notFoundResponse('Document not found.');
            }

            if (! Storage::disk('local')->exists($document->file_path)) {
                return $this->notFoundResponse('File not found.');
            }

            return Storage::disk('local')->download($document->file_path, $document->file_name);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to download document.', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified document metadata and/or file.
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
                return $this->forbiddenResponse('Access denied.');
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return $this->notFoundResponse('Document not found.');
            }

            $updateData = $request->validated();

            // Handle file replacement if a new file is provided
            if ($request->hasFile('file')) {
                // Delete the old file from storage
                if (Storage::disk('local')->exists($document->file_path)) {
                    Storage::disk('local')->delete($document->file_path);
                }

                // Upload the new file
                $file = $request->file('file');
                $fileName = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('documents/'.$franchise->id, $fileName, 'local');

                // Add file information to update data
                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $fileName;
                $updateData['file_extension'] = $file->getClientOriginalExtension();
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();
            }

            $document->update($updateData);

            return $this->successResponse($document->fresh(), 'Document updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update document.', $e->getMessage(), 500);
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
                return $this->forbiddenResponse('Access denied.');
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return $this->notFoundResponse('Document not found.');
            }

            // Delete the file from storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            // Delete the document record
            $document->delete();

            return $this->successResponse(null, 'Document deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete document.', $e->getMessage(), 500);
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
                return $this->forbiddenResponse('Only franchisors can approve documents.');
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return $this->notFoundResponse('Document not found.');
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

            return $this->successResponse($document->fresh(), 'Document approved successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to approve document.', $e->getMessage(), 500);
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
                return $this->forbiddenResponse('Only franchisors can reject documents.');
            }

            // Check if the document belongs to the franchise
            if ($document->franchise_id !== $franchise->id) {
                return $this->notFoundResponse('Document not found.');
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

            return $this->successResponse($document->fresh(), 'Document rejected successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reject document.', $e->getMessage(), 500);
        }
    }
}
