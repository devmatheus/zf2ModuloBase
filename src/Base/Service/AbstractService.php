<?php

namespace Base\Service;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Stdlib\Hydrator;
use Log\Entity\Log as Log;

abstract class AbstractService
{
    /**
     * @var EntityManager
     */
    protected $em;
    protected $entity;

    public function __construct(EntityManager $em = null)
    {
        if (!$this->entity) {
            $classSplited = explode('\\', get_called_class());
            $this->entity = $classSplited[0] . '\Entity\\' . $classSplited[2];
        }
        $this->em = $em;
    }
    
    public function authSession()
    {
        $auth = new AuthenticationService();
        $auth->setStorage(new SessionStorage('admin'));
        return $auth;
    }

    public function insert(array $data)
    {
        $entity = new $this->entity($data);
        
        $this->em->persist($entity);
        $this->em->flush();

        $log = [
            'entity'             => $this->entity,
            'registroId'         => $entity->getId(),
            'registroNovo'       => $entity->toArray(),
            'formularioRecebido' => json_encode($data),
            'acao'               => Log::ACAO_CADASTRO
        ];
        $this->log($log);
        
        return $entity;
    }

    public function update(array $data)
    {
        $entity = $this->em->getReference($this->entity, $data['id']);

        $log = [
            'registroId'         => $entity->getId(),
            'formularioRecebido' => json_encode($data)
        ];

        $hydrator = new Hydrator\ClassMethods;
        $hydrator->hydrate($data, $entity);

        $this->em->persist($entity);
        $this->em->flush();

        $log['registroNovo'] = $entity->toArray();
        $log['acao']         = Log::ACAO_EDICAO;
        $this->log($log);
        return $entity;
    }

    public function delete($id)
    {
        $entity = $this->em->getReference($this->entity, $id);
        if ($entity) {
            $log = [
                'registroId'     => $id,
                'registroAntigo' => $entity->toArray(),
                'acao'           => Log::ACAO_EXCLUSAO
            ];
            $this->log($log);

            $this->em->remove($entity);
            $this->em->flush();
            return $id;
        }
    }

    public function log(array $dados)
    {
        if (!$dados['entity']) {
            $dados['entity'] = $this->entity;
        }

        $auth = $this->authSession();
        if ($auth->hasIdentity()) {
            $usuarioSession = $auth->getIdentity();
            $usuarioEntity  = $this->em->getReference('Usuarios\Entity\Usuario', $usuarioSession['id']);
        }

        $log = [
            'usuario'        => $usuarioEntity,
            'entity'         => $dados['entity'],
            'registroNovo'   => json_encode($dados['registroNovo']),
            'registroAntigo' => json_encode($dados['registroAntigo'])
        ];

        $entity = new Log(array_merge($dados, $log));
        $this->em->persist($entity);
        $this->em->flush();
    }
}
