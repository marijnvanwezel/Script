<?php

namespace MediaWiki\Extension\Script\Engines\PythonStandalone\Exceptions;

use MediaWiki\Extension\Script\Exceptions\ForeignRuntimeException;

/**
 * Exception thrown when an exception occurred in a running Python script.
 */
class PythonRuntimeError extends ForeignRuntimeException {
	public function __construct( string $exceptionMessage, array $trace = [] ) {
		parent::__construct( 'script-python-runtime-error', [$exceptionMessage], $trace );
	}
}