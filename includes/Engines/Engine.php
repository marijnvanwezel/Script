<?php

namespace MediaWiki\Extension\FFI\Engines;

use PPFrame;
use Status;

abstract class Engine {
	/**
	 * @var array The options passed to the engine
	 */
	private $options;

	/**
	 * @param array $options Arbitrary array of options passed to this engine
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * Validates the given source code and reports any syntax errors through
	 * the Status object.
	 *
	 * @param string $script The source to evaluate
	 * @param Status $status
	 * @return void
	 */
	abstract public function validateSource( string $script, Status &$status ): void;

	/**
	 * Executes the given script.
	 *
	 * @param string $script The source of the script to execute
	 * @param string $mainName The name of the main function
	 * @param PPFrame $frame The frame to pass along to the main function
	 * @return string The result of the script execution
	 */
	abstract public function executeScript( string $script, string $mainName, PPFrame $frame ): string;

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
	 * Path to the logo of the language. This is used as an indicator on any pages
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
}
