<?php

namespace Base\TwigExtension;

class ClassTwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            'class' => new \Twig_SimpleFunction('class', array($this, 'getClass'))
        ];
    }

    public function getName()
    {
        return 'class';
    }

    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}
