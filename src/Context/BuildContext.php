<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Context;

class BuildContext {

	public function getNodeVersion(): string {
		return trim(preg_replace('/\s\s+/', ' ', shell_exec('node -v'))) ;
	}

	public function getNpmVersion(): string {
		return trim(preg_replace('/\s\s+/', ' ', shell_exec('npm -v')));
	}

	public function getKrankerlVersion(): string {
		return trim(preg_replace('/\s\s+/', ' ', shell_exec('krankerl --version 2>&1')));
	}

	public function canBuild() {
		
	}
}
