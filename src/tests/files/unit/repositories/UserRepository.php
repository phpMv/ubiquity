<?php

namespace repositories;

use models\User;
use Ubiquity\controllers\Controller;

class UserRepository extends \Ubiquity\orm\repositories\ViewRepository {
	public function __construct(Controller $ctrl) {
		parent::__construct($ctrl,User::class);
	}
}