<?php

namespace App\Enums;

enum ZoneStatus:string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case MAINTENANCE = 'maintenance';
}
