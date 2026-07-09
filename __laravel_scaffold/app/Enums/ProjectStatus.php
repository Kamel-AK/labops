<?php

namespace App\Enums;

enum ProjectStatus:string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case ARCHIVED = 'archived';
    case REJECTED = 'rejected';
}
