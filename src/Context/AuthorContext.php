<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Context;

class AuthorContext {

	public function getAuthorEmail(): string {
		return trim(preg_replace('/\s\s+/', ' ', shell_exec('git config --global user.email')));
	}

	public function getAuthorName(): string {
		return trim(preg_replace('/\s\s+/', ' ', shell_exec('git config --global user.name')));
	}

}
