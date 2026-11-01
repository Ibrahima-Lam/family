<?php

namespace Src;

class Person
{
    private $data = [];
    public function __construct() {}

    public function getDl(array $tab, $dt = null): string
    {
        $dl = "<dl>";
        $dl .= '<dt>' . e($dt) . '</dt>';
        foreach ($tab as  $value) {
            if (is_array($value)) {
                $dl .= $this->getDl($value);
            } else $dl .= "<dd>$value</dd>";
        }

        $dl .= "</dl>";
        return $dl;
    }


    public function getDetail($det, $sum = null, $id = null, bool $opened = true): string
    {
        if ($id !== null && array_key_exists($id, $this->data)) {
            $opened = false;
        }
        $open = $opened ? 'open' : '';
        $detail = "<details id='" . e($id) . "' $open ontoggle='charge(this)'>";
        $detail .= '<summary>' . e($sum) . ' (' . e($id) . ')</summary>';
        $detail .= $det;
        $detail .= '</details>';

        return $detail;
    }
}
