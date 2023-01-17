<?php

namespace Ubiquity\views\engine\micro;

use Ubiquity\utils\base\UFileSystem;
use Ubiquity\views\engine\TemplateEngine;
use Ubiquity\views\engine\TemplateGenerator;

class MicroTemplateEngine extends TemplateEngine {
	private string $viewsFolder;
	private array $parsers = [];

	private function getTemplateParser(string $viewName): TemplateParser {
		if (!isset ($this->parsers [$viewName])) {
			$this->parsers [$viewName] = new TemplateParser ($this->viewsFolder . $viewName);
		}
		return $this->parsers [$viewName];
	}

	public function __construct() {
		$this->viewsFolder = \ROOT . \DS . 'views' . \DS;
	}

	/*
	 * (non-PHPdoc)
	 * @see TemplateEngine::render()
	 */
	public function render(string $viewName, ?array $pData = [], bool $asString = false) {
		if (\is_array($pData)) {
			\extract($pData);
		}
		$content = eval ('?>' . $this->getTemplateParser($viewName)->__toString());
		if ($asString) {
			return $content;
		}
		echo $content;
	}

	public function getBlockNames(string $templateName): array {
		return [];
	}

	public function getCode(string $templateName): string {
		$fileName = $this->viewsFolder . $templateName;
		return UFileSystem::load($fileName);
	}

	public function exists($name): bool {
		$filename = $this->viewsFolder . $name;
		return \file_exists($filename);
	}

	public function addFunction(string $name, $callback, array $options = []): void {
		throw new \BadMethodCallException('addFunction method has no sense with MicroTemplateEngine');
	}

	protected function addFilter(string $name, $callback, array $options = []): void {
		throw new \BadMethodCallException('addFilter method has no sense with MicroTemplateEngine');
	}

	protected function addExtension($extension): void {
		throw new \BadMethodCallException('addExtension method has no sense with MicroTemplateEngine');
	}

	public function getGenerator(): ?TemplateGenerator {
		return null;
	}
}
