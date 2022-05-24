<?php

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception thrown when the requested engine does not exist.
 */
class NoSuchEngineException extends FFIException {
	/**
	 * @param string $ext The identifier of the engine that could not be found
	 */
	public function __construct( string $ext ) {
		parent::__construct( 'ffi-no-such-engine-error', [$ext] );
	}
}
