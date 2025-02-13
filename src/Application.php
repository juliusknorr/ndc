<?php

declare(strict_types=1);

namespace Nextcloud\DevCli;

use DI\Container;
use JsonException;

class Application extends \Symfony\Component\Console\Application {

	/** @var Container */
	private $container;

	public function __construct() {
		parent::__construct('Nextcloud Developer CLI', $this->getComposerJsonVersion());

		$this->container = new Container();

		$this->addCommands(array_map(function (string $className) {
			return $this->container->get($className);
		}, [
			Commands\Configure::class,
			Commands\Status::class,
			Commands\Version::class,
			Commands\Changelog::class,
			Commands\Release::class,

			Commands\Create\App::class,
			Commands\Create\BaseClass::class,
			Commands\Create\Controller::class,
			Commands\Create\Event::class,
			Commands\Create\EventListener::class,
		]));
	}

	private function getComposerJsonVersion(): string {
		$composerFilePath = __DIR__ . '/../composer.json';
		if (!file_exists($composerFilePath)) {
			return '@git_commit_short@';
		}
		try {
			$composerData = json_decode(file_get_contents($composerFilePath), true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			echo 'Application composer file not found';
			die(1);
		}
		return $composerData['version'];
	}
}
