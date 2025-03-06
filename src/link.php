<?php

class Link
{
    private $parent;
    private $linkedParent;
    private $child;
    private $parents1;
    private $parents2;
    private $direct = false;


    public function __construct(array $linkedParent, array $parents1, array $parents2, bool $direct, ?array $parent = null, ?array $child = null)
    {
        $this->linkedParent = $linkedParent;
        $this->parents1 = $parents1;
        $this->parents2 = $parents2;
        $this->direct = $direct;
        $this->parent = $parent;
        $this->child = $child;
    }

    public function getParent(): array
    {
        return $this->parent;
    }

    public function getLinkedParent(): array
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



    public function getChild(): ?array
    {
        return $this->child;
    }

    public function getDifference(string $id_ref): ?string
    {
        $parent = $this->getParent();
        $child = $this->getChild();
        $diff = $child["idg"] - $parent["idg"];
        $ref = ($parent["id"] == $id_ref) ? $parent : $child;
        $diff = ($parent["id"] == $id_ref) ? $diff : -$diff;

        $genre = $ref["sexe"];
        $checked = $this->check($diff);
        return Lien::getText($diff, $genre, $checked);
    }

    private function check(int $diff): bool
    {
        if ($diff != 0) return $this->isDirect();
        $generation = $this->getLinkedParent()["idg"];
        $childrengene = $this->getChild()["idg"];
        if (($childrengene - $generation) == 1)  return true;
        return false;
    }
}
