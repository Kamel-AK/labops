<?php

namespace App\Enums;

enum SpotStatus:string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case MAINTENANCE = 'maintenance';
}
