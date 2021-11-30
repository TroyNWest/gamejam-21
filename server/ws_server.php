<?php  
use WSSC\Components\ServerConfig;
use WSSC\WebSocketServer;

use Game\GameServer;

if (php_sapi_name() != 'cli'){
	die('Command line only');
}

error_reporting(E_ALL ^ E_NOTICE);

define('NL', "\n");

chdir(__DIR__);

try{

// Set a class loader since we don't have vendor/autoload
include 'lib/loader.php';

$config = new ServerConfig();
/* $config->setIsSsl(true)->setAllowSelfSigned(true)
    ->setCryptoType(STREAM_CRYPTO_METHOD_SSLv23_SERVER)
    ->setLocalCert("../apache/conf/ssl.crt/server.crt")
	->setLocalPk("../apache/conf/ssl.key/server.key")
    ->setPort(4444); */

$config->setPort(4444)->setStreamSelectTimeout(0);

$server = new GameServer('data/server.json');

$websocketServer = new WebSocketServer($server, $config);

$websocketServer->run();

}
catch(\Throwable $e){
	echo "Exception: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}" . NL;
	echo $e->getTraceAsString();
}