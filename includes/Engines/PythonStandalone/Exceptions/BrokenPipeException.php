<?php

namespace MediaWiki\Extension\Script\Engines\PythonStandalone\Exceptions;

use MediaWiki\Extension\Script\Exceptions\ScriptException;

/**
 * Exception thrown when the pipe to the external Python process broke.
 */
class BrokenPipeException extends ScriptException {
	public function __construct( ?string $message = null ) {
		if ( $message !== null ) {
			parent::__construct( 'script-broken-pipe-detailed', [$message] );
		} else {
			parent::__construct( 'script-broken-pipe' );
		}
	}
}
