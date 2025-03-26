<?php

namespace App\Custom;

use App\TypeCurrency;

class NumberSpellOut
{
    public function convertir($numero, $idcurrency = NULL){
        $formatterES = new \NumberFormatter("es-ES", \NumberFormatter::SPELLOUT);
        $izquierda = intval(floor($numero));
        $derecha = round(($numero - floor($numero)) * 100, 2);
        if($idcurrency){
            $idcurrency = TypeCurrency::findOrFail($idcurrency);
            return strtoupper($formatterES->format($izquierda)) . " " . strtoupper($idcurrency->name) . " CON " . strtoupper($formatterES->format($derecha)) . " CENTAVOS";
        }
        else
            return strtoupper($formatterES->format($izquierda)) . " PESOS CON " . strtoupper($formatterES->format($derecha)) . " CENTAVOS";
    }
}
