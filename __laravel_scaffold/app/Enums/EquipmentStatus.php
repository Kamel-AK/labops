<?php

namespace App\Enums;

enum EquipmentStatus:string
{
    case AVAILABLE = 'available';
    case IN_USE = 'in_use';
    case BORROWED = 'borrowed';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';
}
