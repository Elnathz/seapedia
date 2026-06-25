<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Available = 'available';
    case Taken = 'taken';
    case Completed = 'completed';
    // Set when the order is auto-refunded (SLA overdue) while a delivery
    // still exists — frees the driver and clears the job board.
    case Cancelled = 'cancelled';
}
