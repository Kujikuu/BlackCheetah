<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
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

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Note created successfully',
            'data' => $note,
        ], 201);
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note): JsonResponse
    {
        $note->load('user:id,name,email', 'lead:id,first_name,last_name');

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note): JsonResponse
    {
        // Check if user can edit this note (only creator or admin)
        if ($note->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this note',
            ], 403);
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

        $note->load('user:id,first_name,last_name');

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data' => $note,
        ]);
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note): JsonResponse
    {
        // Check if user can delete this note (only creator or admin)
        if ($note->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this note',
            ], 403);
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

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }

    /**
     * Remove an attachment from a note.
     */
    public function removeAttachment(Note $note, Request $request): JsonResponse
    {
        // Check if user can edit this note
        if ($note->user_id !== Auth::id() && ! Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this note',
            ], 403);
        }

        $request->validate([
            'attachment_index' => 'required|integer|min:0',
        ]);

        $attachments = $note->attachments ?? [];
        $index = $request->attachment_index;

        if (! isset($attachments[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found',
            ], 404);
        }

        // Delete the file
        if (isset($attachments[$index]['path'])) {
            Storage::disk('public')->delete($attachments[$index]['path']);
        }

        // Remove from array
        array_splice($attachments, $index, 1);

        $note->update(['attachments' => $attachments]);

        return response()->json([
            'success' => true,
            'message' => 'Attachment removed successfully',
            'data' => $note,
        ]);
    }
}
