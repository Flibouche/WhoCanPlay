<?php

namespace App\Enum;

enum SubtypeState: string
{
    case NOT_OPENED = 'Not opened';
    case PENDING = 'Pending';
    case ACCEPTED = 'Processed';
    case DENIED = 'Denied';

    public function toString(): string
    {
        return match($this) {
            self::NOT_OPENED => 'Not opened',
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Processed',
            self::DENIED => 'Denied',
        };
    }
}

foreach (SubtypeState::cases() as $states) {
    echo $states->toString();
}