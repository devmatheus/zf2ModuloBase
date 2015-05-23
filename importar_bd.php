<?php

$configDir = '../../../config/autoload';

if (file_exists($configDir . '/doctrine_orm.local.php')) {
    $configBd  = include $configDir . '/doctrine_orm.local.php';
} elseif (file_exists($configDir . '/doctrine_orm.global.php')) {
    $configBd  = include $configDir . '/doctrine_orm.global.php';
}

$params = $configBd['doctrine']['connection']['orm_default']['params'];

$conexao = new \PDO('mysql:host=' . $params['host'] . ';port=' . $params['port'] . ';dbname=' . $params['dbname'], $params['user'], $params['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);

foreach (new \DirectoryIterator('../') as $fileInfo) {
    $arquivoBd = $fileInfo->getPathname() . '/bd.sql';
    
    if (file_exists($arquivoBd)) {
        $query = file_get_contents($arquivoBd);
        $result = $conexao->exec($query);
        
        echo $arquivoBd . ' importado com sucesso!' . PHP_EOL;
    }
}
