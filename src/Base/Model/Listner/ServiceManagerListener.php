<?php

namespace Base\Model\Listner;

use Zend\ServiceManager\ServiceManager;
 
class ServiceManagerListener
{
    protected $sm;
 
    public function __construct(ServiceManager $sm)
    {
        $this->sm = $sm;
    }
 
    public function postLoad($eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $class = new \ReflectionClass($entity);
        if ($class->implementsInterface('Zend\ServiceManager\ServiceLocatorAwareInterface')) {
            $entity->setServiceLocator($this->sm);
        }
    }
}
