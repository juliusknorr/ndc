<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Context;

use Nextcloud\DevCli\Model\AppInfo;
use Nextcloud\DevCli\Model\PackageJson;
use SimpleXMLElement;

class AppContext {

	private const APP_INFO_PATH = './appinfo/info.xml';
	private const PACKAGE_JSON_PATH = './package.json';

	private $appInfo;
	private $packageJson;
	private $inAppContext = false;

	public function getAppInfo(): ?AppInfo {
		// TODO Handling when running in subdirectory
		if (!$this->appInfo) {
			if (!file_exists(self::APP_INFO_PATH)) {
				return null;
			}
			$xmlContent = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', file_get_contents(self::APP_INFO_PATH));
			$appInfoXml = new SimpleXMLElement($xmlContent);
			$this->appInfo = new AppInfo($appInfoXml, $this);
			$this->inAppContext = true;
		}
		return $this->appInfo;
	}

	public function getPackageJson(): ?PackageJson {
		if (!$this->packageJson) {
			if (!file_exists(self::PACKAGE_JSON_PATH)) {
				return null;
			}
			$this->packageJson = new PackageJson(self::PACKAGE_JSON_PATH);
		}
		return $this->packageJson;
	}

	public function isInAppContext(): bool {
		$this->getAppInfo();
		return $this->inAppContext;
	}

	public function writeAppInfo(?AppInfo $appInfo = null) {
		$appInfo = $appInfo ?? $this->appInfo;
		$appInfo->getXMLElement()->asXML(self::APP_INFO_PATH);
	}

	public function getAppPath(): string {
		// TODO Handling when running in subdirectory
		return getcwd();
	}

	public function getAppNamespace(): string {
		return 'OCA\\' . $this->getAppInfo()->namespace;
	}
}
