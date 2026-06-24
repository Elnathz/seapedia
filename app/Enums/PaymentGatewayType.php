<?php

namespace App\Enums;

enum PaymentGatewayType: string
{
    case Ipaymu = 'ipaymu';
    case Fake = 'fake';
}
