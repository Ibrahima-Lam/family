<?php

namespace Src;

class Html
{
    public static ?string $target = null;
    public static string $linkPrefix = '?p=personne/details/';
    public static string $linkSuffix = '';

    public static function setLinkFormat(string $prefix, string $suffix = ''): void
    {
        self::$linkPrefix = $prefix;
        self::$linkSuffix = $suffix;
    }

    public static function table(array $args, bool $id = false): string
    {
        if (empty($args)) {
            return "<strong>néant</strong>";
        }

        $tt = "<div class='content'>";
        $rendered = 0;
        foreach ($args as $value) {
            if (!is_object($value)) {
                continue;
            }
            $rendered++;

            $personId = $value->idPersonne ?? null;
            $targ = (self::$target !== null && (string) self::$target === (string) $personId) ? 'target' : '';
            $gene = (int) ($value->generation ?? $value->nomGeneration ?? 0);
            $tt .= "<div title='Cliquez sur le nom pour voir les détails' class='element $targ' draggable='true'>";
            $link = $id ? '#' : self::$linkPrefix;
            $suffix = $id ? '' : self::$linkSuffix;
            $tt .= "<span title='Le nom de la personne'><a href='" . e($link) . e($personId) . e($suffix) . "'>"
                . person_name($value->nomPersonne ?? '', $value->surnom ?? null) . "</a></span>";

            $tt .= '<ul>';
            $sexe = (string) ($value->sexe ?? '');
            $tt .= match ($sexe) {
                'M' => "<li title='Son genre'>Homme</li>",
                'F' => "<li title='Son genre'>Femme</li>",
                default => "<li title='Son genre'>Inconnu</li>",
            };
            if ($gene > 0) {
                $tt .= "<li><abbr title='" . e($gene) . "ème génération'>G" . e($gene) . "</abbr></li>";
            }
            $statut = $value->enfantsStatut ?? 'inconnu';
            $manquants = (int) ($value->enfantsManquants ?? 0);
            $tt .= '<li>' . descendance_badge($statut, $manquants) . '</li>';
            $tt .= '</ul>';
            $tt .= '</div>';
        }
        $tt .= '</div>';

        return $rendered > 0 ? $tt : "<strong>néant</strong>";
    }

    public static function option(array $tab, string $cle = 'idGeneration', string $cle2 = 'nomGeneration', mixed $entry = null): string
    {
        $opt = '';
        foreach ($tab as $value) {
            $val = $value[$cle] ?? null;
            $lab = (string) ($value[$cle2] ?? '');
            $surname = trim((string) ($value['surnom'] ?? ''));

            if ($surname !== '') {
                $lab .= ' (' . $surname . ')';
            }

            $el = ($entry !== null && (string) $entry === (string) $val) ? 'selected' : '';

            $opt .= "<option value='" . e($val) . "' $el>" . e($lab) . '</option>';
        }

        return $opt;
    }

    public static function infos(array $args): string
    {
        $pere = [];
        $mere = [];
        $self = [];
        foreach ($args as $value) {
            switch ($value->categorie ?? '') {
                case 'pere':
                    $pere[] = $value;
                    break;
                case 'mere':
                    $mere[] = $value;
                    break;
                default:
                    $self[] = $value;
                    break;
            }
        }

        return self::liste($pere, 'Père') . self::liste($mere, 'Mère') . self::liste($self, 'Lui-même');
    }

    private static function liste(array $tab, ?string $cat = null): string
    {
        if (empty($tab)) {
            return '<strong>néant <br></strong>';
        }

        $ul = "<div class='element'><ul>";
        $ul .= '<h3> ' . e($cat) . ' </h3>';

        foreach ($tab as $value) {
            $ul .= '<li><strong>' . e(ucfirst((string) ($value->nomSupplement ?? ''))) . ':</strong>'
                . e(ucwords((string) ($value->valeur ?? ''))) . '</li>';
        }
        $ul .= '</ul></div>';

        return $ul;
    }
}
