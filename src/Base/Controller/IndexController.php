<?php

namespace Base\Controller;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;

class IndexController extends CrudController
{
    
    public function logadoAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        
        $auth           = new AuthenticationService();
        $auth->setStorage(new SessionStorage('admin'));
        $usuarioSession = $auth->getIdentity();
        $repoUsuarios   = $this->getRepo($config['entities']['Usuarios\Entity\Usuario']);
        $usuario        = $repoUsuarios->find($usuarioSession['id']);

        $repoLog = $this->getRepo($config['entities']['Log\Entity\Log']);
        
        $ultimoLogin = $repoLog->findBy([
            'usuario' => $usuario,
            'acao'    => \Log\Entity\Log::ACAO_LOGIN
        ], ['dataHora' => 'desc'], 1, 1);
        
        $flashMsg = $this->flashMessenger();
        return new ViewModel(array(
            'messagesAlerta'  => $flashMsg->setNamespace('admin-messages')->getMessages(),
            'messagesSucesso' => $flashMsg->setNamespace('admin-messages-sucesso')->getMessages(),
            'ultimoLogin'     => is_object($ultimoLogin[0]) ? $ultimoLogin[0] : null,
            'usuario'         => $usuario
        ));
    }
    
    public function limpaCacheAction()
    {
        $this->delete(ROOT_PATH . '/data/cache/');
        $this->flashMessenger()
                ->setNamespace('admin-messages-sucesso')
                ->addMessage('Cache limpo');
        return $this->redirect()->toRoute('home-admin', array('action' => 'index'));
    }
    
    /**
     * Deleta todos arquivos e pastas de determinado $path.
     * @param string $path
     **/
    public static function delete($path)
    {
        foreach (new \DirectoryIterator($path) as $filesInfo) {
            if ($filesInfo->isDot()) {
                continue;
            }
            
            if (is_dir($filesInfo->getPathname())) {
                self::delete($filesInfo->getPathname());
                rmdir($filesInfo->getPathname());
            }
            unlink($filesInfo->getPathname());
        }
    }
}
