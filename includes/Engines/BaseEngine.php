<?php

namespace MediaWiki\Extension\Script\Engines;

use MediaWiki\Extension\Script\Exceptions\ScriptException;
use MediaWiki\Extension\Script\Exceptions\ValidationException;
use PPFrame;
use Status;

abstract class BaseEngine {
	/**
	 * @var array The options passed to the engine
	 */
	private $options;

	/**
	 * @param array $options Arbitrary array of options passed to this engine
	 *
	 * @note While this class is not a singleton (you can construct it as many times as you want), it is
	 *  usually only constructed once per WebRequest, since instances are cached in the EngineStore class.
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * Executes the given script.
	 *
	 * @param string $script The source of the script to execute
	 * @param string $mainName The name of the main function
	 * @param PPFrame $frame The frame to pass along to the main function
	 * @return string The result of the script execution
	 * @throws ScriptException
	 */
	abstract public function executeScript( string $script, string $mainName, PPFrame $frame ): string;

	/**
	 * Validates the given source code and reports any syntax errors through
	 * the Status object.
	 *
	 * @param string $source The source to evaluate
	 * @param Status $status
	 * @return void
	 * @throws ScriptException
	 */
	abstract public function validateSource( string $source, Status &$status ): void;

	/**
	 * Returns the human-readable name of this language. Only used for display
	 * purposes.
	 *
	 * @return string
	 */
	abstract public function getHumanName(): string;

	/**
	 * Returns the version of this language. Only used for display purposes. May
	 * be NULL to hide the language version.
	 *
	 * @return string|null
	 */
	public function getVersion(): ?string {
		return null;
	}

	/**
	 * Path to the logo of this language. This is used as an indicator on any pages
	 * written in this language. May be NULL to use the human name instead of the
	 * logo.
	 *
	 * @return string|null
	 */
	public function getLogo(): ?string {
		return null;
	}

	/**
	 * Returns the name of the language as understood by the CodeEditor
	 * extension. May return NULL if the language is not supported by
	 * CodeEditor, or to disable the CodeEditor for the language.
	 *
	 * @return string|null
	 */
	public function getCodeEditorName(): ?string {
		return null;
	}

	/**
	 * Returns the name of the language as understood by the GeSHi syntax
	 * highlighter. May return NULL if the language is not supported by
	 * GeSHi or in order to disable highlighting.
	 *
	 * @return string|null
	 */
	public function getGeSHiName(): ?string {
		return null;
	}

	/**
	 * Returns the URL to the help page for this language. May be NULL if there is
	 * no help page.
	 *
	 * @return string|null
	 */
	final public function getHelpUrl(): ?string {
		return $this->options['helpUrl'] ?? null;
	}

	/**
	 * Returns the options passed to the engine.
	 *
	 * @return array
	 */
	final protected function getOptions(): array {
		return $this->options;
	}
}
