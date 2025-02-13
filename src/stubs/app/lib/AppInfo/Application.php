<?php

declare(strict_types=1);

namespace OCA\MyAppSkeleton\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {

	public function __construct() {
		parent::__construct('myappskeleton');
	}

	public function register(IRegistrationContext $context): void {
		// ... registration logic goes here ...

		// Register the composer autoloader for packages shipped by this app, if applicable
		// include_once __DIR__ . '/../../vendor/autoload.php';
	}

	public function boot(IBootContext $context): void {
		// ... boot logic goes here ...
	}

}
