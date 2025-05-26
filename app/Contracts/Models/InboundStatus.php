<?php

namespace App\Contracts\Models;

enum InboundStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case CANCELLED = 'cancelled';
}
