<?php

namespace MediaWiki\Extension\Script\Exceptions;

use Message;
use MWException;
use Throwable;

class ScriptException extends MWException {
	/**
	 * @var Message
	 */
	private $messageObj;

	/**
	 * @param string $messageName The name of the i18n message to throw
	 * @param string[] $messageArgs The arguments to the i18n message
	 * @param Throwable|null $previous The previous throwable used for the exception chaining
	 */
	public function __construct( string $messageName, array $messageArgs = [], Throwable $previous = null ) {
		$this->messageObj = wfMessage( $messageName, $messageArgs );

		parent::__construct( $this->messageObj->parse(), 0, $previous);
	}

	/**
	 * Returns the Message object for this exception.
	 *
	 * @return Message
	 */
	public function getMessageObject(): Message {
		return $this->messageObj;
	}
}
