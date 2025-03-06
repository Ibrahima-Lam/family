<?php

namespace src;

class Lien
{
    const PERE = "père";
    const GRAND_PERE = "grand-père";
    const MERE = "mere";
    const GRAND_MERE = "grand-mere";
    const FILS = "fils";
    const PETIT_FILS = "petit fils";
    const FILLE = "fille";
    const PETITE_FILLE = "petite fille";
    const NEVEU = "neveu";
    const NIECE = "nièce";
    const COUSIN = "cousin";
    const COUSINE = "cousine";
    const FRERE = "frère";
    const SOEUR = "soeur";
    const ARRIERE = " arrière ";
    const PETIT = " petit ";
    const PETITE = " petite ";
    const ONCLE = "oncle";
    const TANTE = "tante";

    public static function getText(?int $diff, ?string $genre = "M", ?bool $checked = false): string
    {
        $genre = ucfirst($genre);
        if ($genre !== "F") {

            switch ($diff) {
                case 1:
                    $lien = $checked ? self::PERE : self::ONCLE;
                    break;
                case 2:
                    $lien = self::GRAND_PERE;
                    break;
                case 0:
                    $lien = $checked ? self::FRERE : self::COUSIN;
                    break;
                case -1:
                    $lien = $checked ? self::FILS : self::NEVEU;
                    break;
                case -2:
                    $lien = self::PETIT_FILS;
                    break;

                default:
                    $lien = "";
                    if ($diff > 2) {
                        for ($i = 1; $i <= $diff - 2; $i++) {
                            $lien .= self::ARRIERE;
                        }
                        $lien .= self::GRAND_PERE;
                    } elseif ($diff < -2) {
                        for ($i = 1; $i <= abs($diff) - 2; $i++) {
                            $lien .= self::PETIT;
                        }
                        $lien .= self::PETIT_FILS;
                    }
                    break;
            }
        } else {
            switch ($diff) {
                case 1:
                    $lien = $checked ? self::MERE : self::TANTE;
                    break;
                case 2:
                    $lien = self::GRAND_MERE;
                    break;
                case 0:
                    $lien = $checked ? self::SOEUR : self::COUSINE;
                    break;
                case -1:
                    $lien = $checked ? self::FILLE : self::NIECE;
                    break;
                case -2:
                    $lien = self::PETITE_FILLE;
                    break;

                default:
                    $lien = "";
                    if ($diff > 2) {
                        for ($i = 1; $i <= $diff - 2; $i++) {
                            $lien .= self::ARRIERE;
                        }
                        $lien .= self::GRAND_MERE;
                    } elseif ($diff < -2) {
                        for ($i = 1; $i <= abs($diff) - 2; $i++) {
                            $lien .= self::PETITE;
                        }
                        $lien .= self::PETITE_FILLE;
                    }
                    break;
            }
        }
        return $lien;
    }
}
