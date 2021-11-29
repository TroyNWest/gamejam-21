<?php

define('LIB_ROOT', __DIR__);

spl_autoload_register(function($class_name){
	if (substr($class_name, 0, 5) == 'WSSC\\'){
		include LIB_ROOT . DIRECTORY_SEPARATOR . 'php-wss' . DIRECTORY_SEPARATOR . str_replace('\\', '/', substr($class_name, 5)) . '.php';
	}
	else{
		if (strpos($class_name, '\\') !== false){
			$class_name = LIB_ROOT . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
		}
		
		echo '[loader] ' . $class_name . '.php' . NL;
		
		include $class_name . '.php';
	}
});