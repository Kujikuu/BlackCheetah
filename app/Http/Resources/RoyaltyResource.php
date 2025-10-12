<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoyaltyResource extends JsonResource
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
            'royalty_number' => $this->royalty_number,
            'billing_period' => $this->period_description,
            'franchisee_name' => $this->franchisee->name ?? 'N/A',
            'store_location' => $this->unit->location ?? $this->franchise->name ?? 'N/A',
            'due_date' => $this->due_date?->format('Y-m-d'),
            'gross_sales' => (float) $this->gross_revenue,
            'royalty_percentage' => (float) $this->royalty_percentage,
            'amount' => (float) $this->total_amount,
            'status' => $this->status,
            'franchise_id' => $this->franchise_id,
            'unit_id' => $this->unit_id,
            'franchisee_id' => $this->franchisee_id,
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'attachments' => $this->attachments ?? [],
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
