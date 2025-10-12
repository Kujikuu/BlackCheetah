<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicalRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'requester_id' => $this->requester_id,
            'requester' => $this->whenLoaded('requester', function () {
                return [
                    'id' => $this->requester->id,
                    'name' => $this->requester->name,
                    'email' => $this->requester->email,
                ];
            }),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return $this->assignedUser ? [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ] : null;
            }),
            'franchise_id' => $this->franchise_id,
            'franchise' => $this->whenLoaded('franchise', function () {
                return $this->franchise ? [
                    'id' => $this->franchise->id,
                    'business_name' => $this->franchise->business_name,
                ] : null;
            }),
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit', function () {
                return $this->unit ? [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name ?? $this->unit->location,
                ] : null;
            }),
            'affected_system' => $this->affected_system,
            'steps_to_reproduce' => $this->steps_to_reproduce,
            'expected_behavior' => $this->expected_behavior,
            'actual_behavior' => $this->actual_behavior,
            'browser_version' => $this->browser_version,
            'operating_system' => $this->operating_system,
            'device_type' => $this->device_type,
            'attachments' => $this->attachments ?? [],
            'internal_notes' => $this->internal_notes,
            'resolution_notes' => $this->resolution_notes,
            'first_response_at' => $this->first_response_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'response_time_hours' => $this->response_time_hours,
            'resolution_time_hours' => $this->resolution_time_hours,
            'satisfaction_rating' => $this->satisfaction_rating,
            'satisfaction_feedback' => $this->satisfaction_feedback,
            'is_escalated' => (bool) $this->is_escalated,
            'escalated_at' => $this->escalated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'is_open' => $this->is_open,
            'is_resolved' => $this->is_resolved,
            'age_in_hours' => $this->age_in_hours,
            'response_time_status' => $this->response_time_status,
        ];
    }
}
