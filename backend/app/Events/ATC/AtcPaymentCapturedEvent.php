<?php

namespace App\Events\ATC;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ATC\AtcTransaction;

class AtcPaymentCapturedEvent
{
    use Dispatchable, SerializesModels;

    public AtcTransaction $transaction;

    public function __construct(AtcTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
