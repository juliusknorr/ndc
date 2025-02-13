<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands\Create;

use Nextcloud\DevCli\Generator\FileCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Controller extends Command {
	use CreateTrait;
	public function __construct(private FileCreator $fileCreator) {
		parent::__construct('create:controller');
		$this
			->setDescription('Creates a new controller class')
			->addArgument('className', InputArgument::REQUIRED)
			->addOption('dry', 'd', InputOption::VALUE_NONE)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->setInputOutput($input, $output);

		return $this->writeClass(
			className: $input->getArgument('className'),
			stub: 'Controller',
			normalizedClassPostfix: 'Controller'
		);
	}
}
