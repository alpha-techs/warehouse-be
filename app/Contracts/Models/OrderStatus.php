<?php

namespace App\Contracts\Models;

enum OrderStatus: string
{
    case DRAFT = 'draft';
    case REQUESTED = 'requested';
    case SENT = 'sent';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
