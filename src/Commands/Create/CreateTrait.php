<?php

namespace Nextcloud\DevCli\Commands\Create;

use Nextcloud\DevCli\Generator\FileCreator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait CreateTrait {
	private FileCreator $fileCreator;

	private InputInterface $input;
	private OutputInterface $output;
	private SymfonyStyle $io;

	protected function setInputOutput(InputInterface $input, OutputInterface $output): void {
		$this->input = $input;
		$this->output = $output;
		$this->io = new SymfonyStyle($input, $output);
	}

	protected function getIO(): SymfonyStyle {
		return $this->io;
	}

	protected function writeClass(string $className, string $stub = 'Class', $context = [], $normalizedClassPostfix = ''): int {
		$writeToFile = true;
		if ($this->input->hasOption('dry') && $this->input->getOption('dry')) {
			$writeToFile = false;
		}

		$classFqn = $this->fileCreator->buildNewClassNamespace(basename($className), dirname($className), $normalizedClassPostfix);
		$result = $this->fileCreator->createClassFromStub($stub, $classFqn, $context, $writeToFile);

		if ($this->io->isVerbose()) {
			$this->preview($classFqn, $result);
		}

		if ($writeToFile) {
			$this->success('✨ Created Class ' . $classFqn);
		}

		return 0;
	}

	protected function preview($header = '', string $content = ''): void {
		$this->io->title($header);
		$this->io->block(
			$content,
			null, 'fg=gray', '  ', false
		);
	}

	protected function debug($message) {
		if (!$this->io->isVeryVerbose()) {
			return;
		}

		$this->io->writeln('<fg=gray>' . $message . '</>');
	}

	protected function info($message) {
		if (!$this->io->isVerbose()) {
			return;
		}

		$this->io->writeln('<fg=cyan>' . $message . '</>');
	}

	protected function success($message) {
		$this->io->writeln('<fg=green>' . $message . '</>');
	}
}
