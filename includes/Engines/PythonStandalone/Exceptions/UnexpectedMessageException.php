<?php

namespace MediaWiki\Extension\Script\Engines\PythonStandalone\Exceptions;

use MediaWiki\Extension\Script\Exceptions\ScriptException;

/**
 * Exception thrown when the external Python process returned an unexpected message. This may indicate a bug in the
 * software.
 */
class UnexpectedMessageException extends ScriptException {
	public function __construct() {
		// TODO: i18n
		parent::__construct( 'script-unexpected-message-error' );
	}
}
