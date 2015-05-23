<?php

namespace Base\TwigExtension;

class StrToLowerTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'strtolower';
    }
    
    public function getFilters()
    {
        return [
            'strtolower' => new \Twig_Filter_Method($this, 'strToLower')
        ];
    }

    public function strToLower($string)
    {
        return strtolower($string);
    }
}
