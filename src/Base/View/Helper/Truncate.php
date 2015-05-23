<?php

namespace Base\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Trunca texto.
 */
class Truncate extends AbstractHelper {

    public function __invoke($texto, $tamanho, $limite = null) {
        /*$pattern = "/<(\w+)>(\s|&nbsp;)*<\/\1>/";
        $texto = preg_replace($pattern, '', $texto);*/
        
        $texto = strip_tags($texto);
        
        if(strlen($texto) > $tamanho):  // se o texto for maior que o "limite" então ele trunca ele
            $texto = $texto . " ";
            $texto = substr($texto, 0, $tamanho);
            $texto = substr($texto, 0, strrpos($texto, ' '));
            $texto .= $limite;
        endif;
        return $texto;
        //return mb_convert_encoding($texto, 'HTML-ENTITIES', 'UTF-8');
    }

}