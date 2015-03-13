<?php
if (! isset ( $_GET ['_url'] ) && isset ( $_SERVER ['PATH_INFO'] ))
	$_GET ['_url'] = $_SERVER ['PATH_INFO'];
try {
	$config = new \Phalcon\Config\Adapter\Ini ( __DIR__ . '/app/config/config.ini' );
	
	$loader = new \Phalcon\Loader ();
	
	$loader->registerDirs ( array (
			__DIR__ . $config->application->modelsDir,
			__DIR__ . $config->application->controllersDir,
			__DIR__ . $config->application->libraryDir,
			__DIR__ . $config->application->pluginsDir
	) )->register ();
	
	$di = new \Phalcon\DI\FactoryDefault ();
	$app = new \Phalcon\Mvc\Micro ( $di );
	$app->get ( '/', function () {
		echo '<h1>Hello DroidHen!</h1>';
	} );
	$app->post ( '/', function () {
		//echo '<h1>Hello DroidHen!</h1>';
		$content=\Utils::get_post_content();
		echo $content;
	} );
	echo $app->handle ();
} catch ( \Phalcon\Exception $e ) {
	echo '<pre>', $e->getTraceAsString (), '</pre>';
}