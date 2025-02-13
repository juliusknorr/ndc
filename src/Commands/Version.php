<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands;

use Nextcloud\DevCli\Context\AppContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Version extends Command {
	protected function configure(): void {
		$this->setName('version')
			->addArgument('level', InputArgument::OPTIONAL)
			->addOption('dry', 'd');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$level = $input->getArgument('level');
		$appContext = new AppContext();
		if (!$appContext->isInAppContext()) {
			$output->writeln('No app context found.');
			return 1;
		}
		$output->writeln('App context: ' . $appContext->getAppInfo()->version);

		if (!$level) {
			return 0;
		}

		$appInfo = $appContext->getAppInfo();
		$newVersion = $this->increaseVersion((string)$appInfo->version, $level);
		if (!empty($level)) {
			$output->writeln('New version ' . $newVersion);
		}

		if (!$input->getOption('dry')) {
			$appInfo->setVersion($newVersion);
			$appContext->writeAppInfo();
			// FIXME adapt appinfo api to packagejson

			$packageJson = $appContext->getPackageJson();
			if ($packageJson) {
				$packageJson
					->setVersion($newVersion)
					->writeBack();
			}
		}

		return 0;
	}

	private function increaseVersion(string $currentVersion, string $level): string {
		$existingVersion = explode('-', $currentVersion);
		$existingPostfix = $existingVersion[1] ?? null;
		$existingVersion = $existingVersion[0] ?? null;
		$existingVersion = array_map(function ($i) { return (int)$i; }, explode('.', $existingVersion));

		$newVersion = $existingVersion;
		if ($level === 'patch') {
			$newVersion[2] = $newVersion[2] + 1;
		} elseif ($level === 'minor') {
			$newVersion[1] = $newVersion[1] + 1;
			if (isset($newVersion[2])) {
				$newVersion[2] = 0;
			}
		} elseif ($level === 'major') {
			$newVersion[0] = $newVersion[0] + 1;
			if (isset($newVersion[1])) {
				$newVersion[1] = 0;
			}
			if (isset($newVersion[2])) {
				$newVersion[2] = 0;
			}
		} else {
			return $level;
		}

		return implode('.', $newVersion);
	}
}
