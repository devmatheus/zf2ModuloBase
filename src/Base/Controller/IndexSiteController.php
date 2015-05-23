<?php

namespace Base\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexSiteController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout('layout/default');
        return new ViewModel([]);
    }
}
