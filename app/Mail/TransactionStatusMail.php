<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public string $previousStatus
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Transaction ' . $this->transaction->transaction_id . ' - Status Updated to ' . ucfirst($this->transaction->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transaction-status',
            with: [
                'transaction'    => $this->transaction,
                'previousStatus' => $this->previousStatus,
                'newStatus'      => $this->transaction->status,
            ]
        );
    }
}
