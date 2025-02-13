<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Generator;

use Nextcloud\DevCli\Context\AppContext;
use Nextcloud\DevCli\Context\AuthorContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class FileCreator {
	private Environment $twig;

	public function __construct(protected AppContext $appContext, protected AuthorContext $authorContext) {
		$loader = new FilesystemLoader(__DIR__ . '/../stubs');
		$this->twig = new Environment($loader, [
			'autoescape' => false,
		]);
	}

	public function validateAppContext(): void {
		if (!$this->appContext->getAppInfo()) {
			throw new \RuntimeException('Not in app context');
		}
	}

	public function copyFromStub(string $file): void {
		copy(__DIR__ . '/../stubs/app/' . $file, $this->appContext->getAppPath() . '/' . $file);
	}

	public function createClassFromStub(string $stub, string $classFqn, $context = [], bool $writeToFile = false): string {
		$this->validateAppContext();

		$className = $this->getClassName($classFqn);
		$classNamespace = $this->getClassBase($classFqn);

		$paths = array_slice(explode('\\', $this->getClassBase($classFqn)), 2);
		$targetPath = $this->getLibPath() . '/' . implode('/', $paths) . '/' . $className . '.php';
		$template = $this->twig->load($stub . '.php.twig');

		$context = array_merge([
			'copyright' => $this->getCopyright(),
			'namespace' => $classNamespace,
			'class' => $className,
		], $context);
		if ($writeToFile) {
			$this->writeFile($targetPath, $template, $context);
		}

		return $template->render($context);
	}

	private function writeFile(string $target, $template, array $context) {
		@mkdir(dirname($target), 0777, true);
		file_put_contents($target, $template->render($context));
	}

	protected function getLibPath(): string {
		return $this->appContext->getAppPath() . '/lib';
	}

	public function buildNewClassNamespace(string $inputName, string $libSubdirectory = null, $normalizeClassPostfix = null): string {
		$inputName = $this->normalizeClass($inputName);
		if ($libSubdirectory === '.') {
			$libSubdirectory = '';
		}
		$baseNamespace = $this->appContext->getAppNamespace() . ($libSubdirectory ? "\\" . $libSubdirectory : '');
		if (str_starts_with($inputName, $baseNamespace)) {
			$inputName = substr($inputName, strlen($this->appContext->getAppNamespace()));
		}
		if (str_starts_with($inputName, $libSubdirectory)) {
			$inputName = substr($inputName, strlen($libSubdirectory));
		}
		$inputName = trim($inputName, '\\');
		return $this->normalizeClass($baseNamespace . "\\" . $inputName, $normalizeClassPostfix);
	}

	protected function normalizeClass(string $class, string $normalizeClassPostfix = null): string {
		$class = str_replace('/', '\\', $class);
		$parts = explode('\\', $class);
		$parts = array_map(fn ($part) => ucfirst($part), $parts);
		$classNameIndex = count($parts) - 1;
		if ($normalizeClassPostfix) {
			// endure that class always ends with postfix if provided
			$parts[$classNameIndex] = str_replace($normalizeClassPostfix . $normalizeClassPostfix, $normalizeClassPostfix, ($parts[$classNameIndex] . $normalizeClassPostfix));
		}
		return implode('\\', $parts);
	}

	protected function getClassName(string $classFqn): string {
		$parts = explode('\\', $classFqn);
		return end($parts);
	}

	protected function getClassBase(string $classFqn): string {
		$parts = explode('\\', $classFqn);
		array_pop($parts);
		return implode('\\', $parts);
	}

	private function getCopyright(): string {
		$template = $this->twig->load('copyright-agpl.twig');
		return $template->render([
			'authorName' => $this->authorContext->getAuthorName(),
			'authorEmail' => $this->authorContext->getAuthorEmail(),
			'year' => date('Y'),
		]);
	}
}
