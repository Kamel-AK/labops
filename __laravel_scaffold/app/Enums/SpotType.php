<?php

namespace App\Enums;

enum SpotType:string
{
    case WORKSTATION = 'workstation';
    case MACHINE = 'machine';
    case DESK = 'desk';
    case BENCH = 'bench';
    case SHARED_TABLE = 'shared_table';
}
