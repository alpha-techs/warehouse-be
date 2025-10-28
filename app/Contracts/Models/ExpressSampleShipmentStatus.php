<?php

namespace App\Contracts\Models;

enum ExpressSampleShipmentStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case BOOKED = 'booked';
    case DISPATCHED = 'dispatched';
    case DELIVERED = 'delivered';
}

