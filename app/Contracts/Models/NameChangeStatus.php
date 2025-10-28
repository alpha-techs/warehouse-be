<?php

namespace App\Contracts\Models;

enum NameChangeStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
