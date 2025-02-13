<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Model;

class Version {
	private $versionArray;
	private $versionModifier;
	public function __construct(string $versionString) {
		[$semVer, $modifier] = strpos($versionString, '-') > 0 ? explode('-', $versionString) : [$versionString, ''];
		$this->versionModifier = $modifier;
		$semVerArray = explode('.', $semVer);
		$this->versionArray = array_map(fn ($i) => (int)$i, [
			$semVerArray[0] ?? 0,
			$semVerArray[1] ?? 0,
			$semVerArray[2] ?? 0,
		]);
	}

	public function getMajor(): int {
		return $this->versionArray[0];
	}

	public function getMinor(): int {
		return $this->versionArray[1];
	}

	public function getPatch(): int {
		return $this->versionArray[2];
	}

	public function __toString(): string {
		return implode('.', $this->versionArray)
			. ($this->versionModifier !== '' ? '-' . $this->versionModifier: '');
	}

	public function setModifier(string $modifier = ''): self {
		$this->versionModifier = '';
		return $this;
	}

	public function increasePatch(): self {
		$this->versionArray[2] += 1;
		return $this;
	}

	public function decreasePatch(): self {
		$this->versionArray[2] -= 1;
		return $this;
	}

	public function increaseMinor(): self {
		$this->versionArray[1] += 1;
		return $this;
	}

	public function decreaseMinor(): self {
		$this->versionArray[1] -= 1;
		return $this;
	}

	public function increaseMajor(): self {
		$this->versionArray[0] += 1;
		return $this;
	}

	public function decreaseMajor(): self {
		$this->versionArray[0] -= 1;
		return $this;
	}
}
