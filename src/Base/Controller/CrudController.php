<?php

namespace Base\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\Query\Expr\Join as Join;

/**
 * Classe de abstração para criação de CRUD.
 * @author Matheus Ferreira Machado <matheusferreira444@gmail.com>
 */
abstract class CrudController extends AbstractActionController
{
    
    const SUCESSO_INSERCAO = MensagensCrud::SUCESSO_INSERCAO;
    const FALHA_INSERCAO   = MensagensCrud::FALHA_INSERCAO;
    const SUCESSO_EDICAO   = MensagensCrud::SUCESSO_INSERCAO;
    const FALHA_EDICAO     = MensagensCrud::FALHA_EDICAO;
    
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Serviços do BD (insert, update, delete). Nome ou objeto do service.
     */
    protected $service;

    /**
     * Entidade do Doctrine.
     */
    protected $entity;

    /**
     * Formulário. Nome ou objeto do service do formulário (casos específicos).
     */
    protected $form;

    /**
     * Rota do Controller.
     * @example O module Agenda tem a Rota "admin-agenda" que usa o Agenda\Controller\IndexController.
     * @var string
     */
    protected $route;

    /**
     * Nome do Controller. Nome ficticio.
     * @example No module Agenda existe o IndexController, porém a rota do module Agenda aponta usa o nome ficticio "agenda" para referenciar o Agenda\Controller\IndexController.
     * @var string
     */
    protected $controller;

    /**
     * Título do modulo para o view.
     * @example O module Noticias tem o título do view "Notícias"
     * @var string
     */
    protected $tituloModulo;

    /**
     * Configurações do grid de registros da Entidade.
     * @var array
     */
    protected $grid;

    /**
     * Campos imprimíveis do formulário.
     * @var array
     */
    protected $camposForm;

    /**
     * Namespace base do module. Exemplo:
     * O namespace atual é "Base\Controller".
     * É usado para setar algumas variáveis padrões, como, Form, Service, Entity, etc.
     * @var string
     */
    public $namespace;

    /**
     * Configurações de upload (Vinculados ao ID).
     * Opções:
     * Caminho absoluto para o envio dos arquivos. Exemplo:
     * $this->upload['diretorio'] = '/../../../../../public/arquivos/noticias'; ou então $this->upload['diretorio'] = 'ROOT_PATH . '/public/arquivos/noticias';
     * Versões dos arquivos.
     * $this->upload['versoes'] = array('front' => array(100, 100));
     * No caso de imagens, se tivesse 2 fotos ficaria assim: front-idDoRegistro-0.jpg front-idDoRegistro-1.jpg
     * E no caso de upload de outros arquivos não é ncecessário passar as resoluções, então ficaria assim:
     * $this->upload['versoes'] = array('downs');
     * @var array
     */
    protected $upload;

    /**
     * Nome do template phtml dos actions.
     * @example array('editar' => 'nomeDoTemplateNoModule.config.php')
     * @var array
     */
    protected $templateMaps;

    /**
     * Rotulos dos actions.
     * @example array('index' => 'Lista de registros')
     * @var array
     */
    protected $rotulos;

    /**
     * Variáveis que vão para a view.
     * @example description$this->varsView = array('javascript' => array());
     * @var array
     */
    protected $varsView = [];

    /**
     * Id do registro que está sendo manipulado.
     * @var string
     */
    protected $id;
    
    /**
     * Rota para redirecionar após persistir com sucesso o registro.
     * @var string
     */
    protected $routeRedirectBeforePersist;

    /**
     * Parâmetros da ação da rota para redirecionar após persistir com sucesso o registro.
     * @var string
     */
    protected $paramsRedirectBeforePersist = [];
    
    /**
     * Array (post do formulário) que será validado e inserido.
     * É útil em casos que se faz necessário tratar a informação antes de gravar.
     * @var array
     */
    protected $dataForm;

    public function indexAction()
    {
        $mensagens = $this->flashMessenger()
                            ->setNamespace('mensagens-admin')
                            ->getMessages();
        
        if (is_array($this->view)) {
            $this->varsView = array_merge($this->varsView, $this->view);
        }
        
        return $this->renderView(array_merge([
            'grid'      => $this->grid,
            'status'    => $this->params()->fromRoute('status'),
            'route'     => $this->route,
            'mensagens' => $mensagens[0],
            'acl'       => $this->getServiceLocator()->get('Acl\Service\Acl')
        ], $this->varsView));
    }

    public function formAction()
    {
        $config = $this->getConfig();
        
        $this->flashMessenger()
                ->clearMessages();
        
        $meta = $this->getEm()->getClassMetadata($this->entity);
        $pk = $meta->getSingleIdentifierFieldName();

        /*
         * Se não tiver passado um objeto então busca o formulário no ServiceManager.
         * Os formulários assim como entidades e services do bd
         * são setados no module.config para fins de ser sobreescrito.
         */
        if (is_string($this->form)) {
            $this->form = $this->getServiceLocator()->get($config['forms'][$this->form]);
        }

        $request = $this->getRequest();
        
        if (!$this->dataForm) {
            $this->dataForm = (count($request->getPost()->toArray()) > 0) ?
                    $request->getPost()->toArray() :
                    [];
        }

        if (null === $this->id) {
            $this->id = ($this->params()->fromRoute($pk, false)) ? $this->params()->fromRoute($pk) : $this->dataForm[$pk];
        }

        if (null === $this->camposForm) {
            $this->camposForm = $this->form->getElements();
        }

        if (!$this->dataForm && $this->id > 0) {
            $this->dataForm = $this->getRepo()->findBy([$pk => $this->id]);
            if (count($this->dataForm) > 0) {
                $this->dataForm = $this->dataForm[0]->toArray();
            } else {
                $this->getResponse()->setStatusCode(404);
            }
        }
        
        $this->form->setData($this->dataForm);

        if ($request->isPost()) {
            if ($this->form->isValid()) {
                if ($this->id) {
                    $resultado = $this->getService()->update($this->dataForm);
                    if ($resultado) {
                        $mensagens['sucesso'] = static::SUCESSO_EDICAO;
                    } else {
                        $mensagens['erro'] = static::FALHA_EDICAO;
                    }
                } else {
                    $resultado = $this->getService()->insert($this->dataForm);
                    if ($resultado) {
                        $mensagens['sucesso'] = static::SUCESSO_INSERCAO;
                    } else {
                        $mensagens['erro'] = static::FALHA_INSERCAO;
                    }
                }
                
                if ($resultado) {
                    $this->id = $resultado->getId();
                }

                if ($this->upload && $resultado) {
                    $this->tratarUpload();
                }

                $this->flashMessenger()
                        ->setNamespace('mensagens-admin')
                        ->addMessage($mensagens);

                if ($this->routeRedirectBeforePersist) {
                    return $this->redirect()->toRoute($this->routeRedirectBeforePersist, $this->paramsRedirectBeforePersist);
                } else {
                    return $this->redirect()->toRoute($this->route, ['action' => 'index']);
                }
            }
        }

        if ($this->id) {
            $entity = $this->getRepo()->find($this->id);
        }

        return $this->renderView(array_merge(['form' => $this->form, 'id' => $this->id, 'camposForm' => $this->camposForm, 'route' => $this->route], $this->varsView));
    }

    public function editarAction() {
        return $this->formAction();
    }

    public function novoAction() {
        return $this->formAction();
    }

    public function gridApiAction() {
        $qb = $this->getEm()->createQueryBuilder();
        
        $entities = $this->getConfig('entities');
        $entity = $entities[$this->entity];
        
        $meta = $this->getEm()->getClassMetadata($entity);
        $pk = $meta->getSingleIdentifierFieldName();
        
        $total = $this->getEm()
                        ->createQuery('SELECT COUNT(e.' . $pk . ') FROM ' . $entity . ' e')
                        ->getSingleScalarResult();

        $response = [
            'recordsTotal' => $total,
            'data'         => []
        ];
        
        $limit = [
            $this->params()->fromQuery('start', 0),
            $this->params()->fromQuery('length', 9)
        ];
        
        $apelidos = array_keys($this->grid['relacoes']);
        
        foreach ($this->grid['campos'] as $nome => $props) {
            if ($props['campo']) {
                $campo = $props['campo'];
            } else {
                $campo = $nome;
            }
            
            $campos[] = $campo;
            
            if (in_array($campo, $apelidos)) {
                $campo = $campo . '_tabela.' . $this->grid['relacoes'][$campo]['campo'] . ' AS ' . $campo;
            } else {
                $campo = 'e.' . $campo;
            }
            
            $campoSelect[] = $campo;
        }

        $query = $qb->select(implode($campoSelect, ','))
                    ->from($entity, 'e');

        foreach ($this->grid['relacoes'] as $apelido => $relacao) {
            $query->leftJoin($entities[$relacao['entity']], $apelido . '_tabela', Join::WITH, $qb->expr()->andx($qb->expr()->eq($apelido . '_tabela.' . $relacao['referencia'], 'e.' . $apelido)));
            $joinString .= ' LEFT JOIN ' . $entities[$relacao['entity']] . ' AS ' . $apelido . '_tabela WITH ' . $apelido . '_tabela.' . $relacao['referencia'] . ' = e.' . $apelido;
        }
        
        $whereCampo      = $this->params()->fromQuery('where_campo');
        $whereValue      = $this->params()->fromQuery('where_valor');
        $whereComparacao = $this->params()->fromQuery('where_comparacao');
        $whereJuncao     = $this->params()->fromQuery('where_juncao');
        
        foreach ($this->grid['filtro'] as $filtro) {
            $whereCampo[] = $filtro['campo'];
            $whereValue[] = $filtro['valor'];
        }

        $whereString = '';

        if ($whereCampo && $whereValue) {
            if (is_array($whereCampo)) {
                for ($i = 0, $len = count($whereCampo); $i<$len; $i++) {
                    $comparacao = $whereComparacao[$i] ? $whereComparacao[$i] : '=';
                    $juncao = ($whereJuncao[$i-1] ? $whereJuncao[$i-1]:'AND');
                    
                    if ($i == 0) {
                        $whereString .= ' WHERE (';
                        $query->where('e.' . $whereCampo[$i] . $comparacao . $whereValue[$i]);
                    } else {
                        $whereString .= ' ' . $juncao . ' ';
                        
                        if (strtoupper($juncao) == 'AND') {
                            $query->andWhere('e.' . $whereCampo[$i] . $comparacao . $whereValue[$i]);
                        } else {
                            $query->orWhere('e.' . $whereCampo[$i] . $comparacao . $whereValue[$i]);
                        }
                    }
                    
                    $whereString .= 'e.' . $whereCampo[$i] . $comparacao . $whereValue[$i];
                }
                $whereString .= ') ';
            } else {
                $comparacao = $whereComparacao[$i] ? $whereComparacao[$i] : '=';
                
                $query->where('e.' . $whereCampo . $comparacao . $whereValue);
                $whereString .= ' WHERE e.' . $whereCampo . $comparacao . $whereValue;
            }
        }
        
        $orderUrl = $this->params()->fromQuery('order');
        if ($orderUrl != null) {
            $coluna = $campos[$orderUrl[0]['column']];
            if (in_array($coluna, $apelidos)) {
                $orderUrl[0]['column'] = $coluna . '_tabela.' . $this->grid['relacoes'][$coluna]['campo'];
            } else {
                $orderUrl[0]['column'] = 'e.' . $coluna;
            }
            
            $query->orderBy($orderUrl[0]['column'], $orderUrl[0]['dir']);
        }

        $search = $this->params()->fromQuery('search')['value'];
        if ($search) {
            foreach ($campos as $i => $campo) {
                if (in_array($campo, $apelidos)) {
                    $campo = $campo . '_tabela.' . $this->grid['relacoes'][$campo]['campo'];
                } else {
                    $campo = 'e.' . $campo;
                }

                if ($i == 0) {
                    $searchWhereString .=  '(' . $campo . ' LIKE \'%' . $search . '%\' ';
                } else {
                    $searchWhereString .= ' OR ' . $campo . ' LIKE \'%' . $search . '%\' ';
                }
            }
            
            $searchWhereString .= ')';
            $whereString .= (strlen($whereString) ? ' AND ': ' WHERE ') . $searchWhereString;
            $query->andWhere($searchWhereString);
        }
        
        $resultCont = $this->getEm()->createQuery('SELECT COUNT(e.' . $pk . ') FROM ' . $entity . ' e ' . $joinString . $whereString);
        $response['recordsFiltered'] = $resultCont->getSingleScalarResult();

         $result = $query->setMaxResults($limit[1])
                            ->setFirstResult($limit[0])
                            ->getQuery()
                            ->getArrayResult();

        $viewHelperManager = $this->getServiceLocator()->get('viewhelpermanager');
        $url               = $viewHelperManager->get('Url');
        $truncate          = $viewHelperManager->get('truncate');

        foreach ($this->grid['acoes']['links'] as $chave => $acao) {
            if ($this->getServiceLocator()
                            ->get('Acl\Service\Acl')
                            ->hasPermission('admin/' . $this->controller, $acao['action'], false) == false) {
                unset($this->grid['acoes']['links'][$chave]);
            }
        }
        
        foreach ($result as $i => $registro) {
            $row = [];
            
            array_map(function ($key) use ($truncate, $registro, &$row) {
                if ($this->grid['campos'][$key]['formatter']) {
                    $formatter = $this->getServiceLocator()->get($this->grid['campos'][$key]['formatter']);
                    $row[$key] = $formatter->set($registro[$key])
                                            ->get();
                } else {
                    $row[$key] = $truncate(strip_tags($registro[$key]), 80, '...');
                }
            }, array_keys($this->grid['campos']));

            if ($this->grid['acoes']['config']['width']) {
                $this->grid['acoes']['config']['width'] = 'width:' . $this->grid['acoes']['config']['width'] . 'px';
            }

            $acoes = '<div style="text-align: center; ' . $this->grid['acoes']['config']['width'] . '">';
            foreach ($this->grid['acoes']['links'] as $acao) {
                
                if (!$acao['campo']) {
                    $acao['campo'] = $pk;
                }
                
                $onClick = (!$this->view['restDisabled']) ? 
                            'onClick="return crud.' . $acao['action'] . '(' . $row[$acao['campo']] . ');"':
                            '';
                $acoes .= '<a href="' . $url('admin-' . $this->controller, ['action' => $acao['action'], $acao['campo'] => $row[$acao['campo']]]) . '" ' . $onClick . ' style="margin: 0 3px;" class="btn btn-xs ' . $acao['class'] . '">' . $acao['label'] . '</a>';
            }
            $acoes .= '</div>';
            
            $row[] = $acoes;

            $response['data'][] = array_values($row);
        }

        return new JsonModel($response);
    }
    
    public function carregarUpload()
    {
        $config = $this->getConfig($this->namespace);
        $diretorio = $config['upload']['diretorio'];
        
        if (!$diretorio) {
            return false;
        }
        
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777);
        }
        
        $files = $this->getRequest()->getFiles();
        $filter = new \Zend\Filter\File\RenameUpload($diretorio);
        $filter->setUseUploadName(true);
        foreach ($files['upload'] as $file) {
            if (!is_string($file['name'])) {
                $file = $file[0];
            }

            if ($file['name'] == null) {
                continue;
            }

            $filter->filter($file);

            $arquivos[] = [
                'nome' => $file['name'],
                'ext' => pathinfo($file['name'], PATHINFO_EXTENSION)
            ];
        }
        return $arquivos;
    }

    public function tratarUpload()
    {
        $arquivos = $this->carregarUpload();
        if (count($arquivos) > 0) {
            $config     = $this->getConfig($this->namespace);
            $versoes    = $config['upload']['versoes'];
            $diretorio  = $config['upload']['diretorio'];
            $tratamento = $config['upload']['tratamento'] ? : 'proporcional';

            CrudController::deletarArquivos($this->id, $diretorio);

            $canvas = new \Base\Canvas();
            foreach ($arquivos as $idArquivo => $arquivo) {
                
                $originalPath = $diretorio . '/' . $arquivo['nome'];
                
                if (getimagesize($originalPath)) {
                    $originalPath = $diretorio . '/original-' . $this->id . '-' . $idArquivo . '.jpg';
                    $canvas->carrega($diretorio . '/' . $arquivo['nome'])
                            ->grava($originalPath)
                            ->resetar();
                    $arquivo['ext'] = 'jpg';
                    unlink($diretorio . '/' . $arquivo['nome']);
                }

                foreach ($versoes as $nome => $resolucao) {
                    if (is_array($resolucao)) {
                        $resolucao = array_slice($resolucao, count($resolucao)-2, 2);
                        
                        if (null == $versoes['admin']) {
                            $versoes['admin'] = [50, 50];
                        }

                        $canvas->carrega($originalPath)
                                ->redimensiona($resolucao[0], $resolucao[1], $tratamento)
                                ->grava($diretorio . '/' . $nome . '-' . $this->id . '-' . $idArquivo . '.jpg')
                                ->resetar();
                    } else {
                        rename($originalPath, $diretorio . '/' . $resolucao . '-' . $this->id . '-' . $idArquivo . '.' . $arquivo['ext']);
                    }
                }
            }
        }
    }

    public static function deletarArquivos($idRegistro, $diretorio)
    {   
        if (file_exists($diretorio)) {
            $directoryIterator = new \IteratorIterator(new \DirectoryIterator($diretorio));
            $arquivos          = new \RegexIterator($directoryIterator, '/^.+-' . $idRegistro . '-\d+.+/');

            iterator_apply($arquivos, function ($arquivo) {
                unlink($arquivo->current()->getPathname());
            }, [$arquivos]);
        }
    }

    public function excluirAction()
    {
        if (!$this->id) {
            $this->id = $this->params()->fromRoute('id');
        }
        
        if ($this->getService()->delete($this->id)) {
            $mensagens['sucesso'] = 'Registro excluido da base de dados.';
        } else {
            $mensagens['erro'] = 'Erro ao tentar excluir registro na base de dados.';
        }
        
        $this->flashMessenger()
                ->setNamespace('mensagens-admin')
                ->addMessage($mensagens);
        return $this->redirect()->toRoute($this->route, ['action' => 'index']);
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    /**
     * @return EntityRepository
     */
    public function getRepo($entity = null)
    {
        if (null === $entity) {
            $entities = $this->getConfig('entities');
            $entity = $entities[$this->entity];
        }
        return $this->getEm()->getRepository($entity);
    }

    /**
     * Seta o script.phtml e os rotulos para a view.
     * @param array $vars
     * @param bool $disableLayout
     * @param string $templateName
     * @return ViewModel
     */
    public function renderView(array $vars, $disableLayout = null, $templateName = null)
    {
        $config = $this->getConfig();
        
        $view = new ViewModel();

        $actionName = $this->getEvent()->getRouteMatch()->getParam('action');
        $rotulos = [
            'index' => 'Lista de Registros',
            'novo' => 'Novo Registro',
            'editar' => 'Editar Registro'
        ];

        $vars['rotuloAction'] = $rotulos[$actionName];
        if ($this->rotulos[$actionName]) {
            $vars['rotuloAction'] = $this->rotulos[$actionName];
        }

        if (!$templateName && !$this->templateMaps[$actionName]) {
            if ($actionName == 'novo' || $actionName == 'editar') {
                $actionName = 'form';
            }

            $templateName = 'view-admin/' . $actionName;
        } elseif (!$templateName && $this->templateMaps[$actionName]) {
            $templateName = $this->templateMaps[$actionName];
        }
        $view->setTemplate($templateName);

        if ($disableLayout) {
            $view->setTerminal(true);
        }

        $vars['tituloModulo'] = $this->tituloModulo;
        $vars['controller'] = $this->controller;
        
        $routeMatch = $this->getEvent()->getRouteMatch();
        $action = $routeMatch->getParam('action');
        $controller = $routeMatch->getParam('controller');
        
        $this->varsView['javascript'] = $config[$this->namespace]['js_controller'][$controller][$action];
        
        return $view->setVariables(array_merge($vars, $this->varsView));
    }

    public function init($namespace)
    {
        $namespace = explode('\\', $namespace);
        $this->namespace = $namespace[0];
        $this->form = $this->namespace . '\Form\\' . $this->namespace;
        $this->service = $this->namespace . '\Service\\' . $this->namespace;
        $this->entity = $this->namespace . '\Entity\\' . $this->namespace;
        $this->controller = strtolower($this->namespace);
        $this->route = 'admin-' . $this->controller;
        $this->upload['diretorio'] = PUBLIC_PATH . '/arquivos/' . $this->controller; // caminho da pasta do modulo, exemplo: public/arquivos/noticias

        $this->grid['acoes']['links']['editar'] = ['action' => 'editar', 'label' => 'Editar', 'class' => 'btn-warning'];
        $this->grid['acoes']['links']['excluir'] = ['action' => 'excluir', 'label' => 'Excluir', 'class' => 'btn-danger'];
    }

    /**
     * Retorna o serviço do BD.
     */
    public function getService()
    {
        if (is_string($this->service)) {
            $services = $this->getConfig('services');
            $this->service = $this->getServiceLocator()->get($services[$this->service]);
        }
        return $this->service;
    }
    
    /*
     * Array de configuração.
     */
    public function getConfig($key = null)
    {
        $config = $this->getServiceLocator()->get('Config');
        return ($key) ? $config[$key]:$config;
    }        
}
