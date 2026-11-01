<?php

namespace App\Support;

final class Descendance
{
    public const INCONNU = 'inconnu';
    public const AUCUN = 'aucun';
    public const COMPLET = 'complet';
    public const INCOMPLET = 'incomplet';

    /** @return list<string> */
    public static function statuses(): array
    {
        return [self::INCONNU, self::AUCUN, self::COMPLET, self::INCOMPLET];
    }

    public static function normalize(mixed $status): string
    {
        $status = mb_strtolower(trim((string) $status));

        return in_array($status, self::statuses(), true) ? $status : self::INCONNU;
    }

    public static function label(mixed $status): string
    {
        return match (self::normalize($status)) {
            self::AUCUN => 'Aucun enfant',
            self::COMPLET => 'Descendance complète',
            self::INCOMPLET => 'Descendance incomplète',
            default => 'Descendance inconnue',
        };
    }

    public static function description(mixed $status): string
    {
        return match (self::normalize($status)) {
            self::AUCUN => 'Il est établi que cette personne n’a pas eu d’enfant.',
            self::COMPLET => 'Tous les enfants connus de cette personne sont ajoutés.',
            self::INCOMPLET => 'Des enfants sont connus mais ne sont pas encore ajoutés à l’arbre.',
            default => 'On ne sait pas encore si tous les enfants de cette personne sont ajoutés.',
        };
    }

    public static function normalizeMissing(mixed $status, mixed $missing): int
    {
        $missing = (int) $missing;

        return match (self::normalize($status)) {
            self::INCOMPLET => max(0, $missing),
            default => 0,
        };
    }

    public static function needsAttention(mixed $status, mixed $missing = 0): bool
    {
        return self::normalize($status) === self::INCOMPLET || (int) $missing > 0;
    }

    public static function isUnresolved(mixed $status): bool
    {
        $status = self::normalize($status);

        return $status === self::INCONNU || $status === self::INCOMPLET;
    }

    public static function badgeClass(mixed $status, mixed $missing = 0): string
    {
        if (self::needsAttention($status, $missing)) {
            return 'badge-warning';
        }

        return match (self::normalize($status)) {
            self::AUCUN, self::COMPLET => 'badge-green',
            default => 'badge-muted',
        };
    }
}
