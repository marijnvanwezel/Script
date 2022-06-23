<?php

namespace MediaWiki\Extension\Script\Exceptions;

/**
 * Exception thrown when the requested script does not exist.
 */
class NoSuchScriptException extends ScriptException {
	public function __construct( string $scriptName ) {
		parent::__construct( 'script-no-such-script-error', [$scriptName] );
	}
}