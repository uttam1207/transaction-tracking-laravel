<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FraudAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public array $fraudResult
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[FRAUD ALERT] High Risk Transaction Detected - ' . $this->transaction->transaction_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.fraud-alert',
            with: [
                'transaction' => $this->transaction,
                'riskScore'   => $this->fraudResult['risk_score'],
                'rules'       => $this->fraudResult['triggered_rules'],
                'recommendation' => $this->fraudResult['recommendation'],
            ]
        );
    }
}
