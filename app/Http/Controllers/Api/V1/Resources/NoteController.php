<?php

namespace App\Http\Controllers\Api\V1\Resources;

use App\Http\Controllers\Api\V1\BaseResourceController;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoteController extends BaseResourceController
{
    /**
     * Check if the authenticated user can manage a note.
     * Users can manage notes if they are:
     * - The creator of the note
     * - An admin
     * - A franchisor managing notes from their sales team
     */
    protected function canManageNote(Note $note): bool
    {
        $user = Auth::user();

        // Admin can manage all notes
        if ($user->hasRole('admin')) {
            return true;
        }

        // Creator can manage their own notes
        if ($note->user_id === $user->id) {
            return true;
        }

        // Franchisor can manage notes from their sales team
        if ($user->hasRole('franchisor')) {
            // Load the note creator if not already loaded
            if (! $note->relationLoaded('user')) {
                $note->load('user');
            }

            $noteCreator = $note->user;

            // Check if the note creator belongs to this franchisor's franchise
            if ($noteCreator && $noteCreator->franchise_id) {
                // Load the franchisor's franchise if not already loaded
                if (! $user->relationLoaded('franchise')) {
                    $user->load('franchise');
                }

                $franchise = $user->franchise;
                if ($franchise && $noteCreator->franchise_id === $franchise->id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Display a listing of notes for a specific lead.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
        ]);

        $notes = Note::where('lead_id', $request->lead_id)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($notes);
    }

    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('notes/attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $note = Note::create([
            'lead_id' => $request->lead_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'attachments' => $attachmentPaths,
        ]);

        $note->load('user:id,name,email');

        return $this->successResponse($note, 'Note created successfully', 201);
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note): JsonResponse
    {
        $note->load('user:id,name,email', 'lead:id,first_name,last_name');

        return $this->successResponse($note);
    }

    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note): JsonResponse
    {
        // Check if user can edit this note
        if (! $this->canManageNote($note)) {
            return $this->forbiddenResponse('Unauthorized to edit this note');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        // Handle new file uploads
        $attachmentPaths = $note->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('notes/attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $note->update([
            'title' => $request->title,
            'description' => $request->description,
            'attachments' => $attachmentPaths,
        ]);

        $note->load('user:id,name,email');

        return $this->successResponse($note, 'Note updated successfully');
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note): JsonResponse
    {
        // Check if user can delete this note
        if (! $this->canManageNote($note)) {
            return $this->forbiddenResponse('Unauthorized to delete this note');
        }

        // Delete associated files
        if ($note->attachments) {
            foreach ($note->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $note->delete();

        return $this->successResponse(null, 'Note deleted successfully');
    }

    /**
     * Remove an attachment from a note.
     */
    public function removeAttachment(Note $note, int $attachmentIndex): JsonResponse
    {
        // Check if user can edit this note
        if (! $this->canManageNote($note)) {
            return $this->forbiddenResponse('Unauthorized to edit this note');
        }

        $attachments = $note->attachments ?? [];

        if (! isset($attachments[$attachmentIndex])) {
            return $this->notFoundResponse('Attachment not found');
        }

        // Delete the file
        if (isset($attachments[$attachmentIndex]['path'])) {
            Storage::disk('public')->delete($attachments[$attachmentIndex]['path']);
        }

        // Remove from array
        array_splice($attachments, $attachmentIndex, 1);

        $note->update(['attachments' => $attachments]);

        return $this->successResponse($note, 'Attachment removed successfully');
    }

    /**
     * Download a note attachment.
     */
    public function downloadAttachment(Note $note, int $attachmentIndex): Response
    {
        // Check if user can access this note
        if (! $this->canManageNote($note)) {
            abort(403, 'Unauthorized to access this note');
        }

        $attachments = $note->attachments ?? [];

        if (! isset($attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found');
        }

        $attachment = $attachments[$attachmentIndex];

        if (! isset($attachment['path'])) {
            abort(404, 'Attachment file not found');
        }

        $filePath = $attachment['path'];

        if (! Storage::disk('public')->exists($filePath)) {
            abort(404, 'Attachment file no longer exists');
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $fileName = $attachment['name'] ?? 'attachment';

        return response()->download($fullPath, $fileName);
    }
}
