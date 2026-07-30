<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case Pending = '0';
    case Completed = '2';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '未読',
            self::Completed => '読了',
        };
    }

    public function badgeClass():string
    {
        return match ($this) {
            self::Pending => 'bg-gray-500 text-white',
            self::Completed => 'bg-red-500 text-white',
        };
    }
}