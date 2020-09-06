<?php
\Ubiquity\cache\CacheManager::startProd ( $config );

\Ubiquity\orm\DAO::setModelsDatabases ( [ 'models\\Fortune' => 'bench','models\\World' => 'bench' ] );

\Ubiquity\cache\CacheManager::warmUpControllers ( [ \controllers\DbMy::class,\controllers\Fortunes_::class ] );

$workerServer->onWorkerStart = function () use ($config) {
	\Ubiquity\orm\DAO::startDatabase ( $config, 'bench' );
	\controllers\DbMy::warmup ();
	\controllers\Fortunes_::warmup ();
};

