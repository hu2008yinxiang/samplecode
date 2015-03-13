<?php

if (! isset ( $_GET ['_url'] ) && isset ( $_SERVER ['PATH_INFO'] ))
	$_GET ['_url'] = $_SERVER ['PATH_INFO'];
try {
	require __DIR__ . '/app/library/ConfigHolder.php';
	$config = new \Phalcon\Config\Adapter\Ini ( \ConfigHolder::getAppPath () . '/app/config/config.ini' );
	\ConfigHolder::init ( $config );

	$loader = new \Phalcon\Loader ();

	$loader->registerDirs ( array (
			\ConfigHolder::getAppPath () . $config->application->modelsDir,
			\ConfigHolder::getAppPath () . $config->application->controllersDir,
			\ConfigHolder::getAppPath () . $config->application->libraryDir,
			\ConfigHolder::getAppPath () . $config->application->pluginsDir
	) )->register ();

	$di = new \Phalcon\DI\FactoryDefault ();

	$di->set ( 'view', function () use($config) {
		$view = new \Phalcon\Mvc\View ();
		$view->setViewsDir ( \ConfigHolder::getAppPath () . $config->application->viewsDir );
		return $view;
	} );

		$di->set ( 'url', function () use($config) {
			$url = new Phalcon\Mvc\Url ();
			$url->setBaseUri ( $config->application->baseUri );
			$url->setStaticBaseUri ( $config->application->staticBaseUri );
			return $url;
		} );

			// $di->set ( 'dispatcher', function () use($di) {
			// $eventsManager = $di->getShared ( 'eventsManager' );
			// $dispatcher = new Phalcon\Mvc\Dispatcher ();
			// $dispatcher->setEventsManager ( $eventsManager );
			// return $dispatcher;
			// } );

			$di->set ( 'session', function () {
				$session = new \Phalcon\Session\Adapter\Files ();
				$session->start ();
				return $session;
			} );

				$di->set ( 'logger', function () {
					return new \Phalcon\Logger\Adapter\File ( \ConfigHolder::getAppPath () . '/app/logs/debug.log' );
				} );

					$di->set ( 'db', function () use($config, $di) {
						$db = new $config->database->databaseClass ( array (
								'host' => $config->database->host,
								'port' => $config->database->port,
								'dbname' => $config->database->dbname,
								'username' => $config->database->username,
								'password' => $config->database->password
						) );
						// $eventsManager = $di->getShared ( 'eventsManager' );
						// $logger = $di->getShared ( 'logger' );
						// $eventsManager->attach ( 'db', function ($event, $db) use($logger) {
						// if ($event->getType () == 'beforeQuery') {
						// $logger->log ( $db->getSQLStatement (), \Phalcon\Logger::INFO );
						// }
						// } );
						// $db->setEventsManager ( $eventsManager );
						return $db;
					} );

						$di->set ( 'redis', function () use($config) {
							$redis = new \Redis\Adapter\Predis ( array (
									'host' => $config->redis->host,
									'port' => $config->redis->port,
									'database' => $config->redis->database,
									'password' => $config->redis->password
							) );
							return $redis;
						} );

							$di->set ( 'flashSession', function () {
								$flashSession=new \Phalcon\Flash\Session();
								$flashSession->setCssClasses(array(
										'error'=>'alert alert-danger',
										'warning'=>'alert alert-warning',
										'notice'=>'alert alert-info',
										'success'=>'alert alert-success'
								));
								return $flashSession;
							} );

								$app = new \Phalcon\Mvc\Application ( $di );
								$app->session;
								//
								$response = $app->handle ();
								//
								echo $response->getContent ();
} catch ( \Phalcon\Exception $e ) {
	echo '<pre>';
	print_r ( $e );
}
