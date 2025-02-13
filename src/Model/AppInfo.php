<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Model;

use Nextcloud\DevCli\Context\AppContext;
use SimpleXMLElement;

class AppInfo {

	public string $id;
	public string $name;
	public string $summary;
	public string $description;
	public string $version;
	public string $namespace;
	private SimpleXMLElement $xmlSource;
	public SimpleXMLElement $dependencies;

	public function __construct(SimpleXMLElement $xmlSource, private AppContext $appContext) {
		if (!isset($xmlSource->id)) {
			throw new \InvalidArgumentException('Invalid appinfo passed');
		}
		$this->xmlSource = $xmlSource;
		$this->id = (string)$xmlSource->id;
		$this->name = (string)$xmlSource->name;
		$this->summary = (string)$xmlSource->summary;
		$this->description = (string)$xmlSource->description;

		$this->dependencies = $xmlSource->dependencies;
		$this->namespace = (string)$xmlSource->namespace;

		$this->version = (string)$xmlSource->version;
	}

	public function getXMLElement(): SimpleXMLElement {
		return $this->xmlSource;
	}

	public function setAppId(string $appId): self {
		$this->xmlSource->id = $appId;
		return $this;
	}

	public function setName(string $name): self {
		$this->xmlSource->name = $name;
		return $this;
	}

	public function setSummary(string $summary): self {
		$this->xmlSource->summary = $summary;
		return $this;
	}

	public function setDescription(string $description): self {
		$this->xmlSource->description = $description;
		return $this;
	}

	public function setVersion(string $version): self {
		$this->version = $version;
		$this->xmlSource->version = $version;
		return $this;
	}

	public function setNamespace(string $namespace): self {
		$this->namespace = $namespace;
		$this->xmlSource->namespace = $namespace;
		return $this;
	}

	public function setAuthor(string $author, string $email): self {
		// FIXME: set authors
		return $this;
	}

	public function write(): self {
		$this->appContext->writeAppInfo($this);
		return $this;
	}
}
