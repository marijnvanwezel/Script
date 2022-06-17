<?php

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception thrown when the pipe to an external process broke.
 */
class BrokenPipeException extends FFIException {
	public function __construct() {
		parent::__construct( 'ffi-broken-pipe' );
	}
}
