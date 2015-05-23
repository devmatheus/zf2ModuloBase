<?php

namespace Base\Service;

use Traversable;
use Zend\Captcha\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

class CaptchaFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('config');

        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        $spec = $config['captcha'];

        if ('image' === $spec['class']) {
            $plugins = $sm->get('viewhelpermanager');
            $urlHelper = $plugins->get('url');

            $font = $spec['options']['font'];

            if (is_array($font)) {
                $rand = array_rand($font);
                $randFont = $font[$rand];
                $font = $randFont;
            }

            $spec['options']['font'] = join('/', array(
                $spec['options']['fontDir'],
                $font
            ));
            
            $spec['options']['imgUrl'] = $urlHelper('captcha');
        }

        $captcha = Factory::factory($spec);
        
        return $captcha;
    }
}
