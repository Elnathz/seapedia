<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Available = 'available';
    case Taken = 'taken';
    case Completed = 'completed';
}
