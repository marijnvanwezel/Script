<?php

namespace MediaWiki\Extension\Script\Exceptions;

/**
 * Exception thrown when an exception occurred in a running (foreign) script.
 */
class ForeignRuntimeException extends ScriptException {
	/**
	 * @var array
	 */
	private $trace;

	public function __construct( string $messageName, array $messageArgs, array $trace = [] ) {
		parent::__construct( $messageName, $messageArgs );

		$this->trace = $trace;
	}

	/**
	 * Returns the stack trace (if available) of the exception that was thrown in the foreign script.
	 *
	 * @return array Array of trace lines
	 */
	public function getForeignTrace(): array {
		return $this->trace;
	}
}