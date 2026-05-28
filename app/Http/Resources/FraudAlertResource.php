<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FraudAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'alert_type'       => $this->alert_type,
            'severity'         => $this->severity,
            'risk_score'       => $this->risk_score,
            'description'      => $this->description,
            'status'           => $this->status,
            'resolution_notes' => $this->resolution_notes,
            'resolved_at'      => $this->resolved_at?->toIso8601String(),
            'created_at'       => $this->created_at->toIso8601String(),
            'transaction'      => $this->whenLoaded('transaction', fn() => [
                'id'             => $this->transaction->id,
                'transaction_id' => $this->transaction->transaction_id,
                'amount'         => $this->transaction->amount,
                'currency'       => $this->transaction->currency,
            ]),
            'assigned_to'      => $this->whenLoaded('assignedTo', fn() => [
                'id'   => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
            ]),
        ];
    }
}
