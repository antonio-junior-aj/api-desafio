<?php
namespace App\Util;

class MaskUtil
{

    /**
     * Função que máscara o CPF
     * 
     * @param string $cpf
     * @return string
     */
    public static function maskCpf($cpf)
    {
        return MaskUtil::mask($cpf, '###.###.###-##');
    }

    /**
     * Função que smascara o CNPJ
     * 
     * @param sting $cnpj
     * @return string
     */
    public static function maskCnpj($cnpj)
    {
        return MaskUtil::mask($cnpj, '##.###.###/####-##');
    }

    public static function mask($val, $mask)
    {
//       https://pt.stackoverflow.com/questions/373479/como-criar-e-implantar-mascara-para-cpf-cnpj-data-e-valores
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i]))
                    $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }

    /**
     * Função que desmascara o CPF
     * 
     * @param sting $cpf
     * @return string
     */
    public static function unMaskCpf($cpf)
    {
        return str_replace(array(".", "-"), "", $cpf);
    }

    /**
     * Função que desmascara o CNPJ
     * 
     * @param string $cnpj
     * @return string
     */
    public static function unMaskCnpj($cnpj)
    {
        return str_replace(array(".", "-", "/"), "", $cnpj);
    }
}
