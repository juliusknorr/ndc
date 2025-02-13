<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands;

use Nextcloud\DevCli\Context\AppContext;
use Nextcloud\DevCli\Context\BuildContext;
use Nextcloud\DevCli\Context\GitContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Command {

	public function __construct(private AppContext $appContext, private GitContext $gitContext, private BuildContext $buildContext) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('status');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->appContext->getAppInfo();
		if ($this->appContext->isInAppContext()) {
			$output->writeln('Current app: ' . $this->appContext->getAppInfo()->id . ' <info>' . $this->appContext->getAppInfo()->version . '</info>');

			$minVersion = $this->appContext->getAppInfo()->dependencies->nextcloud['min-version'] ?? null;
			$maxVersion = $this->appContext->getAppInfo()->dependencies->nextcloud['max-version'] ?? null;
			$versionRange = ($minVersion ?? '') . '-' . ($maxVersion ?? '');
			if ((string)$minVersion === (string)$maxVersion) {
				$versionRange = $maxVersion;
			}
			$output->writeln(' - Nextcloud versions: ' . $versionRange);
			$output->writeln(' - Current branch: ' . $this->gitContext->getBranchName());
			$output->writeln(' - Github repo: ' . $this->gitContext->getGithubOrg() . '/' . $this->gitContext->getGithubRepo());
			$output->writeln('');
			
			$output->writeln('Build requirements: ');
			$output->writeln(' - npm ' . $this->buildContext->getNpmVersion());
			$output->writeln(' - node ' . $this->buildContext->getNodeVersion());
			$output->writeln(' - krankerl ' . $this->buildContext->getKrankerlVersion());

			// TODO validate github token setup
		} else {
			$output->writeln('<error>No app context found.</error>');
		}
		return 0;
	}
}
