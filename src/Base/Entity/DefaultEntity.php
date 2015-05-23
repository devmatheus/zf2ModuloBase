<?php

namespace Base\Entity;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DefaultEntity implements ServiceLocatorAwareInterface
{
    protected $sm;
 
    public function setServiceLocator(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
    }
 
    public function getServiceLocator()
    {
        return $this->sm;
    }
    
    public function excluirArquivos()
    {
        $config = $this->getServiceLocator()->get('config');
        
        $className = get_called_class();
        $classSplited = explode('\\', $className);
        
        /*
        Array
        (
            [0] => DoctrineORMModule
            [1] => Proxy
            [2] => __CG__
            [3] => Noticias
            [4] => Entity
            [5] => Noticias
        )
         */
        
        $diretorio = $config[$classSplited[3]]['upload']['diretorio'];

        \Base\Controller\CrudController::deletarArquivos($this->id, $diretorio);
    }
}
