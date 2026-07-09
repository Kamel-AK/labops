<?php

namespace App\Enums;

enum CheckoutStatus:string
{
    case ACTIVE = 'active';
    case OVERDUE = 'overdue';
    case LOST = 'lost';
    case RETURNED = 'returned';
}
