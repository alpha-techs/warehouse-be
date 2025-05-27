<?php

namespace App\Contracts\Models;

enum ContainerStatus: string
{
    case SHIPPING = 'shipping';
    case ARRIVED = 'arrived';
    case CUSTOMS_CLEARANCE = 'customsClearance';
    case DISCHARGING = 'discharging';
    case DISCHARGED = 'discharged';
    case EMPTY = 'empty';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
}
