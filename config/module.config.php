<?php

namespace Base;

use Zend\Cache\StorageFactory;

return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __NAMESPACE__ => __DIR__ . '/../assets'
            )
        )
    ),
    'router' => array(
        'routes' => array(
            'admin-auth' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin/auth',
                    'defaults' => array(
                        'action' => 'index',
                        'controller' => 'admin/auth'
                    )
                )
            ),
            'home-admin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'action' => 'logado',
                        'controller' => 'home'
                    )
                )
            ),
            'admin-logout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin/logout',
                    'defaults' => array(
                        'action' => 'logout',
                        'controller' => 'admin/auth'
                    )
                )
            ),
            'admin-limpa-cache' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin/limpa-cache',
                    'defaults' => array(
                        'action' => 'limpa-cache',
                        'controller' => 'home'
                    )
                )
            ),
            'site-home' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'action' => 'index',
                        'controller' => 'site/home'
                    )
                )
            )
        )
    ),
    'module_layouts' => array(
        __NAMESPACE__ => 'layout/admin'
    ),
    'controllers' => array(
        'invokables' => array(
            'home'      => 'Base\Controller\IndexController',
            'site/home' => 'Base\Controller\IndexSiteController'
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Cache' => function ($sm) {
                $config = include ROOT_PATH . '/config/application.config.php';
                $cache = StorageFactory::factory(array(
                    'adapter' => array(
                        'name' => $config['cache']['adapter'],
                        'options' => array(
                            'ttl' => 180,
                            'cacheDir' => ROOT_PATH . '/data/cache'
                        )
                    ),
                    'plugins' => array(
                        'exception_handler' => array('throw_exceptions' => false),
                        'Serializer'
                    )
                ));
                return $cache;
            }
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/default'   => __DIR__ . '/../view/layout/layout.phtml',
            'layout/admin'     => __DIR__ . '/../view/layout/layout-admin.twig',
            'error/404'        => __DIR__ . '/../view/error/404.phtml',
            'error/index'      => __DIR__ . '/../view/error/index.phtml',
            'view-admin/index' => __DIR__ . '/../view/base/crud/index.twig',
            'view-admin/form'  => __DIR__ . '/../view/base/crud/form.twig'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    'translator' => array(
        'locale' => 'pt_BR'
    ),
    'navigation' => array(
        'default' => array(
            'configuracoes' => array(
                'label' => 'Configurações',
                'route' => 'admin-usuarios',
                'icon'  => 'glyphicon glyphicon-cog',
                'pages' => array(
                    'limpa-cache' => array(
                        'route' => 'admin-limpa-cache',
                        'label' => 'Limpar Cache'
                    )
                )
            ),
            'conteudo' => array(
                'label' => 'Conteúdo',
                'route' => 'home-admin',
                'icon'  => 'glyphicon glyphicon-list'
            )
        )
    )
);
