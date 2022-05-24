<?php

namespace MediaWiki\Extension\FFI\Engines;

use PPFrame;

/**
 * Represents a script that can be executed.
 *
 * @note Basically a wrapper around a script and an engine that can execute that script.
 */
class Script {
	/**
	 * @var Engine Reference to the engine that can execute this script
	 */
	private $engine;

	/**
	 * @var string The source code of this script
	 */
	private $source;

	/**
	 * @param Engine $engine Reference to the engine used to execute this script
	 * @param string $source The source code of this script
	 */
	public function __construct( Engine $engine, string $source ) {
		$this->engine = $engine;
		$this->source = $source;
	}

	/**
	 * Invokes the specified function in this script.
	 *
	 * @param string $name The name of the function to call
	 * @param PPFrame $frame The frame to pass to the function
	 * @return string The result of the function invocation
	 */
	public function invoke( string $name, PPFrame $frame ): string {
		return $this->engine->executeScript( $this->source, $name, $frame );
	}
}
