<?php

namespace MediaWiki\Extension\FFI\Engines\Python;

use MediaWiki\Extension\FFI\Engines\EngineBase;
use PPFrame;

class PythonEngine extends EngineBase {
	public function executeScript(string $script, string $mainName, PPFrame $frame): string {
		// TODO: Implement executeScript() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getHumanName(): string {
		return 'Python';
	}

	/**
	 * @inheritDoc
	 */
	public function getGeSHiName(): ?string {
		return 'python';
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeEditorName(): string {
		return 'python';
	}
}
