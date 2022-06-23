<?php

namespace MediaWiki\Extension\Script\Exceptions;

/**
 * Exception thrown when the requested engine does not exist.
 */
class NoSuchEngineException extends ScriptException {
	/**
	 * @param string $ext The identifier of the engine that could not be found
	 */
	public function __construct( string $ext ) {
		parent::__construct( 'script-no-such-engine-error', [$ext] );
	}
}
