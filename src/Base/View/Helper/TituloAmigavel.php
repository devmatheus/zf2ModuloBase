<?php

namespace Base\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Retira caracteres especiais do titulo do registro, para ser usado na url.
 */
class TituloAmigavel extends AbstractHelper
{
    public function __invoke($titulo)
    {
        $titulo = strtr(utf8_decode(trim($titulo)), utf8_decode("áàãâéêíóôõúüñçÁÀÃÂÉÊÍÓÔÕÚÜÑÇ"), "aaaaeeiooouuncAAAAEEIOOOUUNC-"); // traduz os caracteres
        $titulo = ereg_replace("[^a-zA-Z0-9-]", "-", $titulo); // retira tudo o que não é letras ou números e substitui por '-'
        
        while (strpos($titulo, '--') !== false) // enquanto tiver duas '--' seguidas ele substitui por '-'
            $titulo = str_replace('--', '-', $titulo);
		if (substr ($titulo, 0, 1) == '-')
			$titulo = substr ($titulo, 1);
            
        /*while (strlen($titulo_limpo) > 50)
            $titulo_limpo = substr($titulo_limpo, 0, strrpos($titulo_limpo, '-'));*/
            
        return strtolower($titulo);
    }
}
