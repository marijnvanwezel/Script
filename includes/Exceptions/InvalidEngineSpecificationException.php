<?php

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception throws when the specification of the engine we're trying to construct is invalid.
 */
class InvalidEngineSpecificationException extends FFIException {
	/**
	 * @param string $ext The identifier of the engine for which the specification is invalid
	 * @param string $reasonName The name of the i18n message that specifies the reason
	 * @param string[] $reasonArgs Any arguments that need to be passed to $reasonName
	 */
	public function __construct( string $ext, string $reasonName, array $reasonArgs ) {
		$reason = wfMessage( $reasonName, $reasonArgs )->parse();

		parent::__construct( 'ffi-invalid-engine-specification-error', [$ext, $reason] );
	}
}
