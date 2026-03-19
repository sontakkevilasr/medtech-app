<?php namespace App\Enums;

enum UserRole: string
{
    case Doctor  = 'doctor';
    case Patient = 'patient';
    case Admin   = 'admin';

    public function label(): string
    {
        return match($this) {
            self::Doctor  => 'Doctor',
            self::Patient => 'Patient',
            self::Admin   => 'Admin',
        };
    }
}
