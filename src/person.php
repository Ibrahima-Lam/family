<?php

namespace src;

class Person
{
    private $data = [];
    public function __construct() {}

    public function getDl(array $tab, $dt = null): string
    {
        $dl = "<dl>";
        $dl .= "<dt>" . $dt ?? null . "</dt>";
        foreach ($tab as  $value) {
            if (is_array($value)) {
                $dl .= $this->getDl($value);
            } else $dl .= "<dd>$value</dd>";
        }

        $dl .= "</dl>";
        return $dl;
    }


    public function getDetail($det, $sum = null, $id = null, $opened = true): string
    {
        if (in_array($id, array_keys($this->data))) $opened = false;
        $open = $opened ? "open" : "";
        $detail = "<details id='$id' $open ontoggle='charge(this)'>";
        $detail .= "<summary>$sum ($id)</summary>";
        $detail .= $det;

        $detail .= "</details>";
        return $detail;
    }
}
