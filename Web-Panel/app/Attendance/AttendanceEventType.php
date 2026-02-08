<?php

namespace App\Attendance;

enum AttendanceEventType: string
{
    case Entry = 'entry';
    case Exit = 'exit';
    case UnknownCardAttempt = 'unknown_card_attempt';

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Wejście',
            self::Exit => 'Wyjście',
            self::UnknownCardAttempt => 'Nieznana karta',
        };
    }

    public static function selectItems(bool $includeAllOption = true): array
    {
        $items = array_map(
            fn (self $case) => ['id' => $case->value, 'name' => $case->label()],
            self::cases()
        );

        if ($includeAllOption) {
            array_unshift($items, ['id' => '', 'name' => 'Wszystkie']);
        }

        return $items;
    }
}
