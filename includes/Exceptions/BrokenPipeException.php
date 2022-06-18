<?php

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception thrown when the pipe to an external process broke.
 */
class BrokenPipeException extends FFIException {
	public function __construct( ?string $message = null ) {
		if ( $message !== null ) {
			parent::__construct( 'ffi-broken-pipe-detailed', [$message] );
		} else {
			parent::__construct( 'ffi-broken-pipe' );
		}
	}
}
