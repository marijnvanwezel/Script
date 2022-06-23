<?php

namespace MediaWiki\Extension\Script\Exceptions;

/**
 * Exception thrown when the engine to invoke a script does not exist (anymore).
 */
class MissingEngineException extends ScriptException {
	public function __construct( string $scriptName ) {
		parent::__construct( 'script-missing-engine-error', [$scriptName] );
	}
}