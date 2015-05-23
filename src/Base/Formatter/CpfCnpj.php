<?php

namespace Base\Formatter;

class CpfCnpj implements Formatter
{
    function set($value)
    {
        $this->value = $value;
        return $this;
    }
    
    function get()
    {
        return self::formatar($this->value);
    }
    
    public static function formatar($campo, $formatado = true)
    {

        $codigoLimpo = ereg_replace("[' '-./ t]", '', $campo);
        $tamanho     = strlen($codigoLimpo);

        if ($formatado) {
            $mascara = ($tamanho == 11) ? '###.###.###-##' : '##.###.###/####-##';

            $indice = -1;
            for ($i = 0, $len = strlen($mascara); $i<$len; $i++) {
                if ($mascara[$i] == '#') {
                    $mascara[$i] = $codigoLimpo[++$indice];
                }
            }
            return $mascara;
        }
        
        return $codigoLimpo;
    }
}
