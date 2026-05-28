<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'transaction_id' => $this->transaction_id,
            'type'           => $this->type,
            'category'       => $this->category,
            'amount'         => (float) $this->amount,
            'fee'            => (float) $this->fee,
            'net_amount'     => (float) $this->net_amount,
            'currency'       => $this->currency,
            'status'         => $this->status,
            'payment_method' => $this->payment_method,
            'sender' => [
                'name'    => $this->sender_name,
                'account' => $this->sender_account,
                'bank'    => $this->sender_bank,
            ],
            'receiver' => [
                'name'    => $this->receiver_name,
                'account' => $this->receiver_account,
                'bank'    => $this->receiver_bank,
            ],
            'fraud' => [
                'is_flagged'  => (bool) $this->is_flagged,
                'risk_score'  => $this->risk_score,
                'risk_level'  => $this->risk_level,
                'reason'      => $this->fraud_reason,
            ],
            'reference'    => $this->reference,
            'country'      => $this->country,
            'ip_address'   => $this->ip_address,
            'processed_at' => $this->processed_at?->toIso8601String(),
            'created_at'   => $this->created_at->toIso8601String(),
            'user'         => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
        ];
    }
}
