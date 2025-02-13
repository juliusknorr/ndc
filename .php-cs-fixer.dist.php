<?php

declare(strict_types=1);

require_once './vendor-bin/php-cs-fixer/vendor/autoload.php';

use Nextcloud\CodingStandard\Config;

$config = new Config();
$config
	->getFinder()
	->ignoreVCSIgnored(true)
	->notPath('build')
	->notPath('composer')
	->notPath('l10n')
	->notPath('lib/Vendor')
	->notPath('vendor')
	->notPath('vendor-bin')
	->in(__DIR__);
return $config;
