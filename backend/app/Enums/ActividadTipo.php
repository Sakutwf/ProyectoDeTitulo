<?php

namespace App\Enums;

enum ActividadTipo: string
{
    case CURSO = 'CURSO';
    case TALLER = 'TALLER';
    case SEMINARIO = 'SEMINARIO';
    case CAMPAÑA = 'CAMPAÑA';
    case OPERATIVO = 'OPERATIVO';
    case COBERTURA = 'COBERTURA';
    case COMUNITARIA = 'COMUNITARIA';

    public function isFormativa(): bool
    {
        return match ($this) {
            self::CURSO, self::TALLER, self::SEMINARIO => true,
            default => false,
        };
    }

    public function isServicio(): bool
    {
        return ! $this->isFormativa();
    }

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
