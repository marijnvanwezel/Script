<?php

namespace MediaWiki\Extension\FFI\Engines;

use PPFrame;

/**
 * Interface implemented by all engines.
 */
interface Engine {
	/**
	 * @param array $options Arbitrary array of options passed to this engine
	 */
	public function __construct( array $options );

	/**
	 * Executes the given script.
	 *
	 * @param string $script The source of the script to execute
	 * @param string $mainName The name of the main function
	 * @param PPFrame $frame The frame to pass along to the main function
	 * @return string The result of the script execution
	 */
	public function executeScript( string $script, string $mainName, PPFrame $frame ): string;

	/**
	 * Returns the human-readable name of this language. Only used for display
	 * purposes.
	 *
	 * @return string
	 */
	public function getHumanName(): string;

	/**
	 * Returns the name of the language as understood by the CodeEditor
	 * extension. May return NULL if the language is not supported by
	 * CodeEditor, or to disable the CodeEditor for the language.
	 *
	 * @return string|null
	 */
	public function getCodeEditorName(): ?string;

	/**
	 * Returns the name of the language as understood by the GeSHi syntax
	 * highlighter. May return NULL if the language is not supported by
	 * GeSHi or in order to disable highlighting.
	 *
	 * @return string|null
	 */
	public function getGeSHiName(): ?string;
}
