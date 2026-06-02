<x-mail::message>
# Transaction Status Update

Hi {{ $transaction->user?->name ?? 'Account Holder' }},

Your transaction has been updated.

<x-mail::panel>
**Transaction ID:** {{ $transaction->transaction_id }}
**Status:** ~~{{ ucfirst($previousStatus) }}~~ → **{{ ucfirst($newStatus) }}**
**Amount:** ₹{{ number_format($transaction->amount, 2) }}
**Date:** {{ $transaction->created_at->format('d M Y, H:i') }}
</x-mail::panel>

@if($newStatus === 'success')
Your transaction has been successfully processed.
@elseif($newStatus === 'failed')
Unfortunately, your transaction could not be processed. Please contact support if you need assistance.
@elseif($newStatus === 'reversed')
Your transaction has been reversed. Any debited amount will be credited back.
@endif

@if($transaction->description)
**Note:** {{ $transaction->description }}
@endif

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
