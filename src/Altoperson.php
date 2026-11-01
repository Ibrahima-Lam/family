<?php

namespace Src;

use App\Models\Personne;
use stdClass;

class Altoperson extends Person
{
    private Personne $model;
    private ?string $id;

    public function __construct(?string $id = null)
    {
        $this->model = new Personne();
        $this->id = $id;
    }

    public function getBackParents($idp): ?string
    {
        if (!$idp) {
            return null;
        }

        $parent = $this->model->findParent($idp);
        if (!$parent) {
            return null;
        }

        return $this->getBackParents($parent->idParent)
            . " <=" . "<a href='#" . e($parent->idPersonne) . "'> " . person_name($parent->nomPersonne, $parent->surnom ?? null) . '</a>';
    }

    /** @param array<int, stdClass> $tab */
    private function getBackParentArray(array &$tab, ?string $idp, ?string $stop = null): ?array
    {
        if (!$idp) {
            return null;
        }

        $parent = $this->model->findParent($idp);
        if ($parent) {
            $idp = $parent->idParent;
            $tab[] = $parent;
            if ($idp !== null && $idp == $stop) {
                return $tab;
            }
            $this->getBackParentArray($tab, $idp, $stop);
        }

        return $tab;
    }

    public function getBackParentString(?string $idp, ?string $stop = null): string
    {
        $parents = [];
        $this->getBackParentArray($parents, $idp, $stop);
        $parents = array_reverse($parents);
        $content = '';
        foreach ($parents as $item) {
            $content .= '<strong>' . person_name($item->nomPersonne, $item->surnom ?? null) . '</strong>';
            $content .= '&lt;=';
        }

        return $content;
    }

    public function getDescent(string $id): ?string
    {
        $element = $this->model->findOne($id);
        if (!$element) {
            return null;
        }

        $children = $this->model->findChildren($id);
        $open = $children ? 'open' : '';
        $nbr = count($children);
        $content = "<dd><details $open><summary>" . person_name($element->nomPersonne, $element->surnom ?? null) . " ($nbr)</summary>";
        if ($children) {
            $generation = $children[0]->nomGeneration ?? '';
            $content .= "<dl class='" . e($generation) . "'><dt>" . e($generation) . '</dt>';
            foreach ($children as $value) {
                $content .= $this->getDescent((string) $value->idPersonne);
            }
            $content .= '</dl>';
        }

        return $content . '</details></dd>';
    }

    /** @param array<int, stdClass> $tab */
    private function getDescentArray(string $id, array &$tab): ?array
    {
        $children = $this->model->findChildren($id);
        foreach ($children as $item) {
            $tab[] = $item;
            $this->getDescentArray((string) $item->idPersonne, $tab);
        }

        return $tab;
    }

    private function checkedDirect(string $id1, string $id2, string &$p): bool
    {
        $t1 = [];
        $t2 = [];
        $this->getDescentArray($id1, $t1);
        $this->getDescentArray($id2, $t2);

        $ids1 = array_map(static fn ($child) => (string) $child->idPersonne, $t1);
        $ids2 = array_map(static fn ($child) => (string) $child->idPersonne, $t2);

        if (in_array($id2, $ids1, true)) {
            $p = $id1;

            return true;
        }

        if (in_array($id1, $ids2, true)) {
            $p = $id2;

            return true;
        }

        return false;
    }

    private function getLinkedParent(string $id1, string $id2): ?stdClass
    {
        $element1 = $this->model->findOne($id1);
        $element2 = $this->model->findOne($id2);
        if (!$element1 || !$element2) {
            return null;
        }

        $t1 = [];
        $t2 = [];
        $this->getBackParentArray($t1, $element1->idParent);
        $this->getBackParentArray($t2, $element2->idParent);

        $byId = [];
        foreach ($t2 as $item) {
            $byId[(string) $item->idPersonne] = $item;
        }

        foreach ($t1 as $item) {
            if (isset($byId[(string) $item->idPersonne])) {
                return $item;
            }
        }

        return null;
    }

    public function getLink(string $id1, string $id2): Link
    {
        $element1 = $this->model->findOne($id1);
        $element2 = $this->model->findOne($id2);

        if (!$element1 || !$element2) {
            return new Link(new stdClass(), [], [], false);
        }

        $linked = [];
        $linked1 = [];
        $linked2 = [];

        $p = '';
        if ($this->checkedDirect($id1, $id2, $p)) {
            $idp = ($p === $id1) ? $element2->idParent : $element1->idParent;
            $stop = ($p === $id1) ? $element1->idParent : $element2->idParent;
            $parent = ($p === $id1) ? $element1 : $element2;
            $child = ($p !== $id1) ? $element1 : $element2;

            $this->getBackParentArray($linked, $idp, $stop);

            return new Link($parent, $linked, $linked, true, $parent, $child);
        }

        $parentlinked = $this->getLinkedParent($id1, $id2);

        $idp1 = $element1->idParent;
        $idp2 = $element2->idParent;
        $idpLinked = $parentlinked->idParent ?? null;

        if ((int) $element1->generation > (int) $element2->generation) {
            $parent = $element2;
            $child = $element1;
        } else {
            $parent = $element1;
            $child = $element2;
        }

        $this->getBackParentArray($linked1, $idp1, $idpLinked);
        $this->getBackParentArray($linked2, $idp2, $idpLinked);

        return new Link($parentlinked ?? new stdClass(), $linked1, $linked2, false, $parent, $child);
    }
}
