<?php
namespace App\Util;

class ValidateUtil
{

    public static function validaCpf($cpf)
    {
        if ($cpf == "11111111111" or $cpf == "22222222222" or $cpf == "33333333333" or $cpf == "44444444444" or $cpf == "55555555555" or $cpf == "66666666666" or $cpf == "77777777777" or $cpf == "88888888888" or $cpf == "99999999999" or $cpf == "00000000000") {
            return false;
        }

        for ($digpos = 10; $digpos < 12; $digpos++) {
            $dig = 0;
            $pos = 0;
            for ($fator = $digpos; $fator > 1; $fator--) {
                $dig = $dig + substr($cpf, $pos, 1) * $fator;
                $pos++;
            }
            if (11 - ($dig % 11) < 10) {
                $dig = 11 - ($dig % 11);
            } else {
                $dig = 0;
            }
            if ($dig != substr($cpf, $digpos - 1, 1)) {
                return false;
            }
        }
        return true;
    }

    public static function validaCnpj($cnpj)
    {

        $RecebeCNPJ = $cnpj;
        $s = "";
        for ($x = 1; $x <= strlen($RecebeCNPJ); $x = $x + 1) {
            $ch = substr($RecebeCNPJ, $x - 1, 1);
            if (ord($ch) >= 48 && ord($ch) <= 57) {
                $s = $s . $ch;
            }
        }

        $RecebeCNPJ = $s;
        if ($RecebeCNPJ == "00000000000000") {
            return false;
        } else {
            $Numero[1] = intval(substr($RecebeCNPJ, 1 - 1, 1));
            $Numero[2] = intval(substr($RecebeCNPJ, 2 - 1, 1));
            $Numero[3] = intval(substr($RecebeCNPJ, 3 - 1, 1));
            $Numero[4] = intval(substr($RecebeCNPJ, 4 - 1, 1));
            $Numero[5] = intval(substr($RecebeCNPJ, 5 - 1, 1));
            $Numero[6] = intval(substr($RecebeCNPJ, 6 - 1, 1));
            $Numero[7] = intval(substr($RecebeCNPJ, 7 - 1, 1));
            $Numero[8] = intval(substr($RecebeCNPJ, 8 - 1, 1));
            $Numero[9] = intval(substr($RecebeCNPJ, 9 - 1, 1));
            $Numero[10] = intval(substr($RecebeCNPJ, 10 - 1, 1));
            $Numero[11] = intval(substr($RecebeCNPJ, 11 - 1, 1));
            $Numero[12] = intval(substr($RecebeCNPJ, 12 - 1, 1));
            $Numero[13] = intval(substr($RecebeCNPJ, 13 - 1, 1));
            $Numero[14] = intval(substr($RecebeCNPJ, 14 - 1, 1));

            $soma = $Numero[1] * 5 + $Numero[2] * 4 + $Numero[3] * 3 + $Numero[4] * 2 + $Numero[5] * 9 + $Numero[6] * 8 + $Numero[7] * 7 +
                $Numero[8] * 6 + $Numero[9] * 5 + $Numero[10] * 4 + $Numero[11] * 3 + $Numero[12] * 2;

            $soma = $soma - (11 * (intval($soma / 11)));

            if ($soma == 0 || $soma == 1) {
                $resultado1 = 0;
            } else {
                $resultado1 = 11 - $soma;
            }

            if ($resultado1 == $Numero[13]) {
                $soma = $Numero[1] * 6 + $Numero[2] * 5 + $Numero[3] * 4 + $Numero[4] * 3 + $Numero[5] * 2 + $Numero[6] * 9 +
                    $Numero[7] * 8 + $Numero[8] * 7 + $Numero[9] * 6 + $Numero[10] * 5 + $Numero[11] * 4 + $Numero[12] * 3 + $Numero[13] * 2;
                $soma = $soma - (11 * (intval($soma / 11)));
                if ($soma == 0 || $soma == 1) {
                    $resultado2 = 0;
                } else {
                    $resultado2 = 11 - $soma;
                }

                if ($resultado2 == $Numero[14]) {
                    return true; // cnpj ok?
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        //Fim do validar CNPJ
        return false;
    }
}
