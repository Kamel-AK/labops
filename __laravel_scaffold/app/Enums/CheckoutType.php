<?php

namespace App\Enums;

enum CheckoutType:string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
}
