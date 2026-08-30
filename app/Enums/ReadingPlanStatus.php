<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => '未読',
            self::Completed => '読了',
            self::Expired => '失効',
        };
    }

    public function badgeClass():string
    {
        return match ($this) {
            self::InProgress => 'bg-gray-500 text-white',
            self::Completed => 'bg-red-500 text-white',
            self::Expired => 'bg-grey-500 text-black',
        };
    }
}