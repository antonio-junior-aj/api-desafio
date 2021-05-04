<?php

namespace App\Util;

class MaskUtil {

    public static function maskCpf($cpf) {
        $cpfPt1 = substr($cpf, 0, 3);
        $cpfPt2 = substr($cpf, 3, 3);
        $cpfPt3 = substr($cpf, 6, 3);
        $cpfPt4 = substr($cpf, 9, 3);
        $cpfMasked = $cpfPt1 . "." . $cpfPt2 . "." . $cpfPt3 . "-" . $cpfPt4;
        return $cpfMasked;
    }

    public static function maskCnpj($cnpj) {
        $cnpjPt1 = substr($cnpj, 0, 2);
        $cnpjPt2 = substr($cnpj, 2, 3);
        $cnpjPt3 = substr($cnpj, 5, 3);
        $cnpjPt4 = substr($cnpj, 8, 4);
        $cnpjPt5 = substr($cnpj, 12, 2);
        $cnpjMasked = $cnpjPt1 . "." . $cnpjPt2 . "." . $cnpjPt3 . "/" . $cnpjPt4 . "-" . $cnpjPt5;
        return $cnpjMasked;
    }

    public static function unMaskCpf($cpf) {
        $cpf = explode(".", $cpf);
        $cpf2 = explode("-", $cpf[2]);
        $cpfUnMasked = $cpf[0] . $cpf[1] . $cpf2[0] . $cpf2[1];
        return $cpfUnMasked;
    }

    public static function unMaskCnpj($cnpj) {
        $cnpj = explode(".", $cnpj);
        $cnpj2 = explode("/", $cnpj[2]);
        $cnpj3 = explode("-", $cnpj2[1]);
        $cnpjUnMasked = $cnpj[0] . $cnpj[1] . $cnpj2[0] . $cnpj3[0] . $cnpj3[1];
        return $cnpjUnMasked;
    }

}
