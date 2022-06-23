<?php

namespace MediaWiki\Extension\Script\Exceptions;

/**
 * Exception thrown when no function is specified in the #script parser function.
 */
class MissingFunctionNameException extends ScriptException {
	public function __construct() {
		parent::__construct( 'script-missing-function-name-error' );
	}
}