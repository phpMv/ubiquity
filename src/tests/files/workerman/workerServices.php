<?php
\Ubiquity\cache\CacheManager::startProd ( $config );
\Ubiquity\controllers\Router::start ();
\Ubiquity\orm\DAO::setModelsDatabases ( [ \models\bench\Fortune::class => 'bench',\models\bench\World::class => 'bench' ] );

\Ubiquity\cache\CacheManager::warmUpControllers ( [ \controllers\bench\DbMy::class,\controllers\bench\Fortunes_::class ] );

$workerServer->onWorkerStart = function () use ($config) {
	\Ubiquity\orm\DAO::startDatabase ( $config, 'bench' );
	\controllers\bench\DbMy::warmup ();
	\controllers\bench\Fortunes_::warmup ();
};

