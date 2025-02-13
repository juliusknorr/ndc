<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands;

use Nextcloud\DevCli\Context\AuthorContext;
use Nextcloud\DevCli\Context\ConfigContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Configure extends Command {

	public function __construct(private AuthorContext $authorContext, private ConfigContext $configContext) {
		parent::__construct('configure');
		$this
			->addOption('reconfigure', 'r', InputOption::VALUE_NONE)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$emptyConfig = $this->configContext->getConfig() === [];

		if ($emptyConfig || $input->getOption('reconfigure')) {
			$helper = $this->getHelper('question');

			if ($emptyConfig) {
				$output->writeln('No configuration found, setup wizard starting…');
				$output->writeln('');
			}

			$output->writeln('<info>GitHub Access</info>');
			$output->writeln('Please generate a personal access token from https://github.com/settings/tokens');
			$question = new Question('Personal access token:', '');
			$accessToken = $helper->ask($input, $output, $question);
			if ($accessToken === '') {
				$accessToken = $this->configContext->getGithubToken();
			}
			$this->configContext->setGithubToken($accessToken);
			$this->configContext->write();

			$output->writeln('<info>Configured</info>');

			return 0;
		}

		$output->writeln('GitHub token: ' . $this->configContext->getGithubToken());

		$output->writeln('Git user name: ' . $this->authorContext->getAuthorName());
		$output->writeln('Git user email: ' . $this->authorContext->getAuthorEmail());
		return 0;
	}
}
