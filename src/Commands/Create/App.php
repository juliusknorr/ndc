<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands\Create;

use Nextcloud\DevCli\Context\AppContext;
use Nextcloud\DevCli\Context\AuthorContext;
use Nextcloud\DevCli\Generator\FileCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class App extends Command {
	use CreateTrait;

	private string $appPath;

	public function __construct(
		private FileCreator   $fileCreator,
		private AuthorContext $authorContext,
		private AppContext    $appContext
	) {
		parent::__construct('create:app');

		$this
			->setName('create:app')
			->setDescription('Create a new app')
			->addArgument('appId', InputArgument::OPTIONAL)
			->addOption('path', 'p', InputOption::VALUE_OPTIONAL)
			->addOption('namespace', null, InputOption::VALUE_OPTIONAL)
			->addOption('force', 'f', InputOption::VALUE_NONE)
		;

	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->setInputOutput($input, $output);
		$helper = $this->getHelper('question');

		$appId = $input->getArgument('appId');
		if ($appId === null) {
			$question = new Question('Please enter the app id:', 'my_app');
			$appId = $helper->ask($input, $output, $question);
		}

		$path = realpath($input->getOption('path') ?? '');
		$namespace = $input->getOption('namespace') ?? $this->appIdToNamespace($appId);

		if (!$input->getOption('force')) {
			$question = new ConfirmationQuestion('Create the app (y/N)', false);
			if (!$helper->ask($input, $output, $question)) {
				return Command::FAILURE;
			}
		}

		$this->createAppDirectory($path, $appId);

		$this->appContext->getAppInfo()
			->setAppId($appId)
			->setName($appId)
			->setNamespace($namespace)
			->setAuthor($this->authorContext->getAuthorName(), $this->authorContext->getAuthorEmail())
			->write();

		if ($this->getIO()->isVerbose()) {
			$this->preview('appinfo/info.xml', $this->appContext->getAppInfo()->getXMLElement()->asXML());
		}

		$this->gitInit();

		$output->writeln('<info>✅ Created app directory for ' . $appId . '</info>');
		return 0;
	}

	private function createAppDirectory(string $path, string $appId): void {
		$appDiretory = $path . '/' . $appId;
		if (is_dir($appDiretory)) {
			//throw new \RuntimeException('Folder already exists');
		}

		$this->info('📁 Creating app directory…');
		@mkdir($appDiretory);
		// Everything after happens in the app directory
		@chdir($appDiretory);

		$this->info('📄 Creating app info…');
		@mkdir($appDiretory . '/appinfo');
		$this->fileCreator->copyFromStub('appinfo/info.xml');
		$this->debug('- Creating base folder structure…');

		@mkdir($appDiretory . '/lib');
		@mkdir($appDiretory . '/img');
		@touch($appDiretory . '/README.md');
		$this->fileCreator->copyFromStub('COPYING');
		$this->appPath = $appDiretory;
	}

	private function appIdToNamespace(mixed $inputName): string {
		$inputName = lcfirst($inputName);
		$inputName = str_replace(' ', '', ucwords(str_replace('_', ' ', $inputName)));
		$inputName = str_replace(' ', '', ucwords(str_replace('-', ' ', $inputName)));
		return $inputName;
	}

	private function gitInit() {
		$this->info('🍴 Creating local git repository…');
		$process = new Process(['git', 'init'], $this->appPath);
		$process->run();

		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}

		$process = new Process(['git', 'add', '.'], $this->appPath);
		$process->run();

		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}

		$process = new Process(['git', 'commit', '-m', 'chore: Initial commit'], $this->appPath);
		$process->run();

		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}
	}

}
