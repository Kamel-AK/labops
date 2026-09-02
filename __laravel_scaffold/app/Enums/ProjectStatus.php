<?php

namespace App\Enums;

enum ProjectStatus:string
{
    case PROPOSED = 'proposed';
    case PENDING_APPROVAL = 'pending_approval';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';
}
