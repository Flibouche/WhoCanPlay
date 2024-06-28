<?php

namespace App\Enum;

enum FeatureState: string
{
    case NOT_OPENED = 'Not opened';
    case PENDING = 'Pending';
    case ACCEPTED = 'Processed';
    case DENIED = 'Denied';

}