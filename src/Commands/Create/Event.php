<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands\Create;

use Nextcloud\DevCli\Generator\FileCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Event extends Command {
	use CreateTrait;

	public function __construct(private FileCreator $fileCreator) {
		parent::__construct('create:event');

		$this
			->setDescription('Creates a new event class')
			->addArgument('className', InputArgument::REQUIRED, 'Event class name to create, e.g. MyAppLoadedEvent');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->setInputOutput($input, $output);

		return $this->writeClass($input->getArgument('className'), 'Event');
	}
}
