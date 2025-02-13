<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Generator;

use Github\Exception\RuntimeException;
use Nextcloud\DevCli\Context\AppContext;
use Nextcloud\DevCli\Context\GitContext;
use Spatie\Async\Pool;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Changelog {

	private const MAX_THREAD_BUFFER = 10485760;
	private $changelogEntries = [];

	public function __construct(private AppContext $appContext, private GitContext $gitContext) {
		
	}

	/**
	 * Fetch commits between base and current version
	 */
	public function fetchPullrequests(string $branch = null, $previousVersion, OutputInterface $output = null) {
		$branch = $branch ?? $this->gitContext->getBranchName();
		$orgName = $this->gitContext->getGithubOrg();
		$repoName = $this->gitContext->getGithubRepo();

		/** @var Repo $repo */
		$repo = $this->gitContext->getClient()->api('repo');
		try {
			$output->writeln("Fetching git history for $repoName...");
			$diff = $repo->commits()->compare($orgName, $repoName, $previousVersion, $branch);
		} catch (RuntimeException $e) {
			if ($e->getMessage() === 'Not Found') {
				$output->writeln('<error>Could not find base or head reference on ' . $repoName. '.</error>');
				die(1);
			}
			throw $e;
		}

		// Extract pull request number from merge commits
		foreach ($diff['commits'] as $commit) {
			$fullMessage = $commit['commit']['message'];
			[$firstLine,] = explode("\n", $fullMessage, 2);
			if (strpos($firstLine, 'Merge pull request #') === 0) {
				$firstLine = substr($firstLine, 20);
				list($number, ) = explode(" ", $firstLine, 2);
				$pullRequests[] = $number;
				$output->writeln('PR ' . $number);
			}
		}
		return $pullRequests;
	}

	public function processPullRequests($pullRequests, $output) {
		// Processing individual pull requests
		$client = $this->gitContext->getClient();
		$orgName = $this->gitContext->getGithubOrg();
		$repoName = $this->gitContext->getGithubRepo();

		$pool = Pool::create()
			->concurrency(5)
			->timeout(5);

		$progressBar = new ProgressBar($output, count($pullRequests));
		$progressBar->start();
		foreach ($pullRequests as $prNumber) {
			$pool->add(function () use ($client, $prNumber, $orgName, $repoName, $output) {
				try {
					return $client->api('pull_request')->show($orgName, $repoName, $prNumber);
				} catch (\Throwable $e) {
					return $e;
				}
			}, self::MAX_THREAD_BUFFER)->then(function ($output) use ($progressBar, $prNumber) {
				if (!is_array($output)) {
					// TODO: error handling
					return;
				}
				$this->processPullRequest($output);
				$progressBar->advance();
			})->catch(function (Throwable $exception) use ($progressBar, $prNumber, $output) {
				$output->writeln('Failed to fetch ' . $prNumber . ': ' . $exception->getMessage());
				$progressBar->advance();
				return true;
			});
		}
		$pool->wait();
		$progressBar->finish();
		$output->writeln('');
		$output->writeln('');
		$output->writeln('');
	}


	private function processPullRequest(array $prData): void {
		$labels = array_map(fn ($label) => $label['name'], $prData['labels']);
		if (in_array('enhancement', $labels, true)) {
			if (!isset($this->changelogEntries['added'])) {
				$this->changelogEntries['added'] = [];
			}
			$this->changelogEntries['added'][] = $prData;
		} elseif (in_array('bug', $labels, true)) {
			if (!isset($this->changelogEntries['fixed'])) {
				$this->changelogEntries['fixed'] = [];
			}
			$this->changelogEntries['fixed'][] = $prData;
		} elseif (in_array('dependencies', $labels, true)) {
			if (!isset($this->changelogEntries['dependencies'])) {
				$this->changelogEntries['dependencies'] = [];
			}
			$this->changelogEntries['dependencies'][] = $prData;
		} else {
			if (!isset($this->changelogEntries['other'])) {
				$this->changelogEntries['other'] = [];
			}
			$this->changelogEntries['other'][] = $prData;
		}
	}

	public function filterOutEntries(callable $filter): void {
		$entries = [];
		foreach ($this->changelogEntries as $category => $categoryEntries) {
			foreach ($categoryEntries as $entry) {
				if ($filter($entry)) {
					if (!isset($entries[$category])) {
						$entries[$category] = [];
					}
					$entries[$category][] = $entry;
				}
			}
		}
		$this->changelogEntries = $entries;
	}
	

	public function getChangelogEntry(string $version, OutputInterface $output): void {
		$output->writeln('## ' . $version);
		$output->writeln('');
		$this->printChangelogCategory('Added', 'added', $output);
		$this->printChangelogCategory('Fixed', 'fixed', $output);
		$this->printChangelogCategory('Dependencies', 'dependencies', $output);
		$this->printChangelogCategory('Other', 'other', $output);
	}

	private function printChangelogCategory(string $name, string $identifier, OutputInterface $output): void {
		if (!isset($this->changelogEntries[$identifier])) {
			return;
		}

		$output->writeln('### ' . $name);
		$output->writeln('');
		foreach ($this->changelogEntries[$identifier] as $pullRequest) {
			$title = $pullRequest['title'];
			$user = ' @' . $pullRequest['user']['login'];

			// strip branch prefix [stableXX] that may be added by the backport bot
			$branchPrefix = '[' . $this->gitContext->getBranchName(). '] ';
			if (mb_strpos($title, $branchPrefix) === 0) {
				$title = mb_substr($title, mb_strlen($branchPrefix));
			}

			if ($user === ' @backportbot-nextcloud[bot]' || $user === ' @backportbot[bot]') {
				$user = '';
			}

			$output->writeln('- ' . $title . $user . ' [#' . $pullRequest['number'] . '](' . $pullRequest['html_url'] . ')');
		}
		$output->writeln('');
	}



}
