<?php

namespace App\Traits\Helpers;

use ValueError;

trait EnumHelper
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return collect(self::cases())->mapWithKeys(static fn ($case) => [$case->value => $case->label()])->toArray();
    }

    public function label(): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $this->name)));
    }

    public static function getLabelByValue(int $value): string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->label();
            }
        }

        throw new ValueError("{$value} is not a valid value for enum " . self::class);
    }

    public static function getNameByValue(int $value): string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->name;
            }
        }

        throw new ValueError("{$value} is not a valid value for enum " . self::class);
    }

    public static function getValueByName(string $name): string
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case->value;
            }
        }

        throw new ValueError("{$name} is not a valid name for enum " . self::class);
    }
}
