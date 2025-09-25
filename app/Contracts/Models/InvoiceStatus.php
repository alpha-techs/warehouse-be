<?php

namespace App\Contracts\Models;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case CANCELLED = 'cancelled';
    case PAID = 'paid';
}
