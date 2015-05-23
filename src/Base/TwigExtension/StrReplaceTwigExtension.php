<?php

namespace Base\TwigExtension;

class StrReplaceTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'str_replace';
    }
    
    public function getFunctions()
    {
        return [
            'str_replace' =>  new \Twig_Function_Method($this, 'strReplace')
        ];
    }

    public function strReplace($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject);
    }
}
