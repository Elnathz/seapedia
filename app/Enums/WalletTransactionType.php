<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case Topup = 'topup';
    case Payment = 'payment';
    case Refund = 'refund';
    case Income = 'income';
    case Earning = 'earning';
    case Reversal = 'reversal';
    case Adjustment = 'adjustment';
}
