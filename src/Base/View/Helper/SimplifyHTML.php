<?php

namespace Base\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Comprime o HTML de saída.
 */
class SimplifyHTML extends AbstractHelper
{
    public static function simplify()
    {
        $data = ob_get_contents();
        ob_clean();
        //echo utf8_encode(preg_replace(array('/<!--(.*)-->/Uis', '/\/\*.*\*\//Uis', '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'), array('', '', '>', '<', '\\1'), utf8_decode($data)));
        echo $data;
        ob_end_flush();
    }
}
