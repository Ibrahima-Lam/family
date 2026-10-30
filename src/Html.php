<?php

namespace Src;

class Html
{
  public static $target;
  public static function table(array $args, $id = false): string
  {

    if (!is_array($args)) return "neant";
    if (empty($args)) return "<strong>néant</strong>";

    $tt = "<div class='content'>";
    foreach ($args as  $value) {
      if (!$value) return "néant";
      $targ = (self::$target == ($value->idPersonne ?? null)) ? "target" : "";
      $gene = $value->generation ?? '';
      $tt .= "<div title='cliquez sur le Nom pour voir les details qui lui correspondent' class='element $targ' draggable='true'>";
      // $target=($value->idPersonne)?
      $link = ($id) ? "#" : "personne.php?id=";
      $tt .= "<span title='Le Nom de la personne'><a href='" . e($link) . e($value->idPersonne ?? '') . "'>  " . e($value->nomPersonne ?? '') . "</a></span>";
      $sexe = $value->sexe ?? '';
      $card = ($gene == 1) ? "ere" : "eme";
      $tt .= "<ul>";
      $tt .= ($sexe == "M") ? "<li title='Son genre'>Homme</li>" : (!($sexe == "F") ?: "<li>Femme</li>");
      $tt .= "<li><abbr title='" . e($gene) . e($card) . " Generation'>G" . e($gene) . "</abbr></li>";
      $tt .=  "</ul>";
      $tt .= "</div>";
    }
    $tt .= "</div>";
    return $tt;
  }
  public static function option(array $tab, string $cle = "idGeneration", string $cle2 = "nomGeneration", $entry = null): string
  {

    $opt = "";
    foreach ($tab as  $value) {
      $val = $value[$cle];
      $lab = $value[$cle2];
      $el = ($entry == $val && $entry) ? "selected" : "";

      $opt .= "<option value='" . e($val) . "' $el>" . e($lab) . "</option>";
    }
    return $opt;
  }

  public static function infos($args)
  {
    $pere = [];
    $mere = [];
    $self = [];
    foreach ($args as  $value) {
      switch ($value->categorie) {
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
    return self::liste($pere, "Pére") . self::liste($mere, "Mere") . self::liste($self, "Lui-meme");
  }
  private static function liste($tab, $cat = null)
  {

    if (empty($tab) || !is_array($tab)) return "<strong>néant <br></strong>";
    $ul = "<div class='element'><ul>";
    $ul .= "<h3> " . e($cat) . " </h3>";

    foreach ($tab as  $value) {
      $ul .= "<li><strong>" . e(ucfirst($value->nomSupplement)) . ":</strong>" . e(ucwords($value->valeur)) . "</li>";
    }
    $ul .= "</ul></div>";
    return $ul;
  }
}
