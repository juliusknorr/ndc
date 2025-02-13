<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Context;

class ConfigContext {
	private array $config = [];

	public function __construct() {
		$configPath = $_SERVER['HOME'] . '/.nextcloud/ndc';
		if (file_exists($configPath)) {
			$this->config = include $configPath;
		}
	}

	public function getConfig(): array {
		return $this->config;
	}

	public function getGithubToken(): ?string {
		if (isset($this->config['github_token'])) {
			return $this->config['github_token'];
		}

		return null;
	}

	public function setGithubToken(string $githubToken): void {
		$this->config['github_token'] = $githubToken;
	}


	public function write() {
		// TODO write config
		echo "<?php return " . var_export($this->config, true) . ";\n";
	}
}
