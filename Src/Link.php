<?php

namespace Src;

use stdClass;

class Link
{
    private $parent;
    private $linkedParent;
    private $child;
    private $parents1;
    private $parents2;
    private $direct = false;


    public function __construct(?stdClass $linkedParent, array $parents1, array $parents2, bool $direct, ?stdClass $parent = null, ?stdClass $child = null)
    {
        $this->linkedParent = $linkedParent;
        $this->parents1 = $parents1;
        $this->parents2 = $parents2;
        $this->direct = $direct;
        $this->parent = $parent;
        $this->child = $child;
    }

    public function getParent(): stdClass
    {
        return $this->parent;
    }

    public function getLinkedParent(): stdClass
    {
        return $this->linkedParent;
    }

    public function getFirstParents(): array
    {
        return $this->parents1;
    }

    public function getSecondParents(): array
    {
        return $this->parents2;
    }

    public function isDirect(): bool
    {
        return $this->direct;
    }



    public function getChild(): ?stdClass
    {
        return $this->child;
    }

    public function getDifference(string $id_ref): ?string
    {
        $parent = $this->getParent();
        $child = $this->getChild();

        $diff = $child->generation - $parent->generation;
        $ref = ($parent->idPersonne == $id_ref) ? $parent : $child;
        $diff = ($parent->idPersonne == $id_ref) ? $diff : -$diff;

        $genre = $ref->sexe;
        $checked = $this->check($diff);
        return Lien::getText($diff, $genre, $checked);
    }

    private function check(int $diff): bool
    {
        if ($diff != 0) return $this->isDirect();
        $generation = $this->getLinkedParent()->generation;
        $childrengene = $this->getChild()->generation;
        if (($childrengene - $generation) == 1)  return true;
        return false;
    }
}
