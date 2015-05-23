<?php

namespace Base\TwigExtension;

class JsonDecodeTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'json_decode';
    }
    
    public function getFilters()
    {
        return [
            'json_decode' =>  new \Twig_Function_Method($this, 'jsonDecode')
        ];
    }

    /**
     * @param string $str
     * @return array
     */
    public function jsonDecode($str) {
        return json_decode($str, true);
    }
}
