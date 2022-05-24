<?php

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception thrown when the engine to invoke a script does not exist (anymore).
 */
class MissingEngineException extends FFIException {
	public function __construct( string $scriptName ) {
		parent::__construct( 'ffi-missing-engine-error', [$scriptName] );
	}
}