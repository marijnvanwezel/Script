<?php

namespace MediaWiki\Extension\FFI\Exceptions;

use MWException;
use Throwable;

class FFIException extends MWException {
	/**
	 * @param string $messageName The name of the i18n message to throw
	 * @param string[] $messageArgs The arguments to the i18n message
	 * @param string $messageFallback The fallback message to throw if the message cache can't be called by the exception
	 * @param Throwable|null $previous The previous throwable used for the exception chaining
	 */
	public function __construct( string $messageName, array $messageArgs, string $messageFallback, Throwable $previous = null ) {
		parent::__construct( $this->msg( $messageName, $messageFallback, ...$messageArgs ), 0, $previous);
	}
}
