<?php namespace App\Enums;

enum AppointmentStatus: string
{
    case Booked      = 'booked';
    case Confirmed   = 'confirmed';
    case Completed   = 'completed';
    case Cancelled   = 'cancelled';
    case NoShow      = 'no_show';
    case Rescheduled = 'rescheduled';

    public function badgeColor(): string
    {
        return match($this) {
            self::Booked      => 'blue',
            self::Confirmed   => 'green',
            self::Completed   => 'gray',
            self::Cancelled   => 'red',
            self::NoShow      => 'orange',
            self::Rescheduled => 'yellow',
        };
    }
}
