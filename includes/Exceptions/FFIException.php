<?php

namespace MediaWiki\Extension\FFI\Exceptions;

use MWException;
use Throwable;

class FFIException extends MWException {
	/**
	 * @param string $messageName The name of the i18n message to throw
	 * @param string[] $messageArgs The arguments to the i18n message
	 * @param Throwable|null $previous The previous throwable used for the exception chaining
	 */
	public function __construct( string $messageName, array $messageArgs = [], Throwable $previous = null ) {
		parent::__construct( wfMessage( $messageName, $messageArgs )->parse(), 0, $previous);
	}
}
