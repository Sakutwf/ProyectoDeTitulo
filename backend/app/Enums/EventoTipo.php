<?php

namespace App\Enums;

enum EventoTipo: string
{
    case FORMATIVO = 'FORMATIVO';
    case SERVICIO = 'SERVICIO';

    public static function tryFromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        return self::tryFrom(strtoupper(trim($value)));
    }
}
