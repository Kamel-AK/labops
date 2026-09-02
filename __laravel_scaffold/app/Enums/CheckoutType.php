<?php

namespace App\Enums;

enum CheckoutType:string
{
    case IN_LAB = 'in_lab';
    case BORROW = 'borrow';
}
