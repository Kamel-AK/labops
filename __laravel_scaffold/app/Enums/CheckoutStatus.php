<?php

namespace App\Enums;

enum CheckoutStatus:string
{
    case ACTIVE = 'active';
    case OVERDUE = 'overdue';
    case RETURNED = 'returned';
}
