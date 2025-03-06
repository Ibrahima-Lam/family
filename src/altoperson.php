<?php

namespace src;

use App\Modele\Personne;

use stdClass;

class Altoperson extends Person
{
    private $model;



    public function __construct(?string $id = null)
    {
        $this->model = new Personne();
    }




    public function getBackParents($idp): ?string
    {
        if (!$idp) return null;
        $parent = $this->model->findParent($idp);
        if ($parent) {
            return $this->getBackParents($parent->idParent) . " <=" . "<a href='#{$parent->idPersonne}'> {$parent["nom"]}</a>";
        } else {
            return null;
        }
    }


    private function getBackParentArray(array &$tab, ?string $idp, ?string $stop = null): ?array
    {
        if (!$idp) return null;
        $parent = $this->model->findParent($idp);

        if ($parent) {
            $idp = $parent->idParent;
            $id = $parent->idPersonne;
            $tab[] = $parent;
            if ($idp == $stop) return $tab;
            $this->getBackParentArray($tab, $idp, $stop);
        }
        return $tab;
    }

    public function getBackParentString(?string $idp, ?string $stop = null): string
    {
        $parents = [];
        $this->getBackParentArray($parents, $idp, $stop);
        $parents = array_reverse($parents);
        $content = "";
        foreach ($parents as $item) {
            $content .= "<strong>{$item["nom"]}</strong>";
            $content .= "&lt;=";
        }
        return $content;
    }

    public function getDescent(string  $id): ?string
    {
        $element = $this->model->findOne($id);
        $children = $this->model->findChildren($id);
        $open = ($children) ? "open" : "";
        $nbr = count($children);
        $content =  "<dd><details $open><summary>{$element["nom"]} ({$nbr})</summary>";
        if ($children) {
            $content .= "<dl class='{$children[0]["nomGeneration"]}'><dt>{$children[0]["nomGeneration"]}</dt>";
            foreach ($children as  $value) {
                $content .=  $this->getDescent($value->idPersonne);
            }
            $content .= "</dl>";
        }
        return $content . "</details></dd>";
    }

    private function getDescentArray(string $id, array &$tab): ?array
    {

        $children = $this->model->findChildren($id);
        if ($children) {
            foreach ($children as $item) {
                $tab[] = $item;
                $this->getDescentArray($item->idPersonne, $tab);
            }
        }
        return $tab;
    }
    private function checkedDirect(string $id1, string $id2, string &$p): bool
    {
        $t1 = [];
        $t2 = [];
        $children1 = $this->getDescentArray($id1, $t1);
        $children2 = $this->getDescentArray($id2, $t2);

        $ids1 = array_column($children1, "id");
        $ids2 = array_column($children2, "id");

        if (in_array($id2, $ids1)) {
            $p = $id1;
            return true;
        } elseif (in_array($id1, $ids2)) {
            $p = $id2;
            return true;
        } else return false;
    }

    private function getLinkedParent(string $id1, string $id2): ?stdClass
    {
        $element1 = $this->model->findOne($id1);
        $element2 = $this->model->findOne($id2);



        $idp1 = $element1->idParent;
        $idp2 = $element2->idParent;

        $t1 = [];
        $t2 = [];
        $this->getBackParentArray($t1, $idp1);
        $this->getBackParentArray($t2, $idp2);
        foreach ($t1 as $item) {
            foreach ($t2 as $val) {
                if ($item->idPersonne == $val->idPersonne) return $item;
            }
        }
        return null;
    }
    public function getLink(string $id1, string $id2): Link
    {

        $element1 = $this->model->findOne($id1);
        $element2 = $this->model->findOne($id2);

        $linked = [];
        $linked1 = [];
        $linked2 = [];

        $p = "";
        if ($this->checkedDirect($id1, $id2, $p)) {

            $idp = ($p == $id1) ? $element2->idParent : $element1->idParent;
            $stop = ($p == $id1) ? $element1->idParent : $element2->idParent;
            $parent = ($p == $id1) ? $element1 : $element2;
            $child = ($p != $id1) ? $element1 : $element2;

            $this->getBackParentArray($linked, $idp, $stop);
            return new Link($parent, $linked, $linked, true, $parent, $child);
        }

        $parentlinked = $this->getLinkedParent($id1, $id2);

        $idp1 = $element1->idParent;
        $idp2 = $element2->idParent;
        $idpLinked = $parentlinked->idParent ?? null;
        if ($element1->generation > $element2->generation) {
            $parent = $element2;
            $child = $element1;
        } else {
            $parent = $element1;
            $child = $element2;
        }

        $this->getBackParentArray($linked1, $idp1, $idpLinked);
        $this->getBackParentArray($linked2, $idp2, $idpLinked);

        return new Link($parentlinked, $linked1, $linked2, false, $parent, $child);
    }
}
