<?php

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception thrown when the requested script does not exist.
 */
class NoSuchScriptException extends FFIException {
	public function __construct( string $scriptName ) {
		parent::__construct( 'ffi-no-such-script-error', [$scriptName] );
	}
}