<?php

namespace App\Enum;

enum SubtypeState: string
{
    case NOT_OPENED = 'Not opened';
    case PENDING = 'Pending';
    case ACCEPTED = 'Processed';
    case DENIED = 'Denied';

}