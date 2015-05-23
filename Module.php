<?php

namespace Base;

use Zend\Mvc\MvcEvent;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function mvcPreDispatch(MvcEvent $event)
    {   
        $serviceLocator = $event->getTarget()->getServiceLocator();
        $acl = $serviceLocator->get('Acl\Service\Acl');
        
        $acl->setMvcEvent($event);
        $acl->hasPermission();
        return $acl->hasPermission();
    }

    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $em = $sm->get('doctrine.entitymanager.orm_default');
        $dem = $em->getEventManager();
        $dem->addEventListener(array(\Doctrine\ORM\Events::postLoad), new Model\Listner\ServiceManagerListener($sm));
        
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', \Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'mvcPreDispatch'), 100);
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            } elseif (strpos(strtolower($moduleNamespace), 'admin') !== false) {
                $controller->layout('layout/admin');
            } else { 
                $controller->layout('layout/default');
            }
        }, 98);
                
        $e->getApplication()->getServiceManager()->get('viewhelpermanager')->setFactory('controllerName', function($sm) use ($e) {
            return new View\Helper\ControllerName($e->getRouteMatch());
        });
                
        $translator = $e->getApplication()->getServiceManager()->get('translator');
        $translator->addTranslationFile('phpArray', ROOT_PATH . '/vendor/zendframework/zendframework/resources/languages/pt_BR/Zend_Validate.php', 'default', 'pt_BR');
        $translator->addTranslationFile('phpArray', ROOT_PATH . '/vendor/zendframework/zendframework/resources/languages/pt_BR/Zend_Captcha.php', 'default', 'pt_BR');
        \Zend\Validator\AbstractValidator::setDefaultTranslator(new \Zend\Mvc\I18n\Translator($translator));
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            )
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'Truncate'       => 'Base\View\Helper\Truncate',
                'TituloAmigavel' => 'Base\View\Helper\TituloAmigavel'
            ),
            'factories' => array(
                'Config' => function($serviceManager) {
                    return new View\Helper\Config($serviceManager);
                }
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'upload' => function ($sm) {
                    $config = $sm->get('Config');
                    return (object)$config['upload'];
                },
                'Base\Mail\Transport' => function ($sm) {
                    $config = $sm->get('Config');

                    $transport = new SmtpTransport;
                    $options = new SmtpOptions($config['mail']);
                    $transport->setOptions($options);

                    return $transport;
                },
                'Base\Mail\Mail' => function ($sm) {
                    return new Mail\Mail($sm->get('Base\Mail\Transport'), $sm->get('View'));
                },
                'Base\Service\CaptchaFactory' => function () {
                    return new Service\CaptchaFactory;
                },
                'Base\Formatter\DateTime' => function () {
                    return new Formatter\DateTime;
                },
                'Base\Formatter\Date' => function () {
                    return new Formatter\Date;
                },
                'Base\Formatter\CpfCnpj' => function () {
                    return new Formatter\CpfCnpj;
                }
            )
        );
    }
}
