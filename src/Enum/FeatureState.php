<?php

namespace App\Enum;

enum FeatureState: string
{
    case NOT_OPENED = 'Not opened';
    case PENDING = 'Pending';
    case PROCESSED = 'Processed';
    case DENIED = 'Denied';

    public static function toString(self $value): string
    {
        return $value->value;
    }
}