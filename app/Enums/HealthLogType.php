<?php namespace App\Enums;

enum SubscriptionPlan: string
{
    case Basic      = 'basic';
    case Premium    = 'premium';
    case Enterprise = 'enterprise';

    public function features(): array
    {
        return match($this) {
            self::Basic => [
                'max_patients'       => 50,
                'prescriptions'      => true,
                'appointments'       => true,
                'timelines'          => false,
                'excel_export'       => false,
                'whatsapp_reminders' => false,
            ],
            self::Premium => [
                'max_patients'       => 500,
                'prescriptions'      => true,
                'appointments'       => true,
                'timelines'          => true,
                'excel_export'       => true,
                'whatsapp_reminders' => true,
            ],
            self::Enterprise => [
                'max_patients'       => -1,   // unlimited
                'prescriptions'      => true,
                'appointments'       => true,
                'timelines'          => true,
                'excel_export'       => true,
                'whatsapp_reminders' => true,
                'analytics'          => true,
                'multi_clinic'       => true,
            ],
        };
    }
}


enum HealthLogType: string
{
    case BP          = 'bp';
    case Sugar       = 'sugar';
    case Weight      = 'weight';
    case Oxygen      = 'oxygen';
    case Temperature = 'temperature';
    case Pulse       = 'pulse';

    public function unit(): string
    {
        return match($this) {
            self::BP          => 'mmHg',
            self::Sugar       => 'mg/dL',
            self::Weight      => 'kg',
            self::Oxygen      => '%',
            self::Temperature => '°C',
            self::Pulse       => 'bpm',
        };
    }

    public function hasTwoValues(): bool
    {
        return $this === self::BP;
    }
}
