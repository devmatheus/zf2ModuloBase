<?php

namespace Base\TwigExtension;

class InstanceofTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'instanceof';
    }
    
    public function getTests()
    {
        return [
            'instanceof' =>  new \Twig_Function_Method($this, 'isInstanceof')
        ];
    }

    /**
     * @param $var
     * @param $instance
     * @return bool
     */
    public function isInstanceof($var, $instance)
    {
        return  $var instanceof $instance;
    }
}
