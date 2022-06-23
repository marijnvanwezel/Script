<?php

namespace MediaWiki\Extension\Script\MediaWiki\ParserFunctions;

use MediaWiki\Extension\Script\Exceptions\ScriptException;
use MediaWiki\Extension\Script\Exceptions\MissingFunctionNameException;
use MediaWiki\Extension\Script\Exceptions\NoSuchScriptException;
use MediaWiki\Extension\Script\ScriptFactory;
use Parser;
use PPFrame;
use Title;
use UtfNormal\Validator;
use Xml;

/**
 * Responsible for the execution of the "#script" parser function.
 */
class ScriptParserFunction {
	public const Script_ERRORS_DATA_KEY = 'ScriptErrors';

	/**
	 * @var ScriptFactory
	 */
	private $scriptFactory;

	/**
	 * @param ScriptFactory $scriptFactory
	 */
	public function __construct( ScriptFactory $scriptFactory ) {
		$this->scriptFactory = $scriptFactory;
	}

	/**
	 * Executes the "#script" parser function.
	 *
	 * @param Parser $parser The current MediaWiki parser
	 * @param PPFrame $frame The current PPFrame
	 * @param array $args The arguments supplied to the parser function
	 * @return string The result of the function invocation, or a formatted error
	 */
	public function execute( Parser $parser, PPFrame $frame, array $args ): string {
		try {
			return $this->doExecute( $frame, $args );
		} catch ( ScriptException $exception ) {
			return $this->handleException( $parser, $exception );
		}
	}

	/**
	 * Executes the "#script" parser function, and throws an exception upon failure.
	 *
	 * @param PPFrame $frame The current PPFrame
	 * @param array $args The arguments supplied to the parser function
	 * @return string The result of the function invocation
	 * @throws ScriptException
	 */
	private function doExecute( PPFrame $frame, array $args ): string {
		if ( count ( $args ) < 2 ) {
			throw new MissingFunctionNameException();
		}

		$scriptName = trim( $frame->expand( $args[0] ) );

		if ( empty( $scriptName ) ) {
			throw new MissingFunctionNameException();
		}

		$scriptFunction = trim( $frame->expand( $args[1] ) );
		$scriptTitle = Title::makeTitleSafe( NS_SCRIPT, $scriptName );

		if ( $scriptTitle === null ) {
			throw new NoSuchScriptException( $scriptName );
		}

		$script = $this->scriptFactory->newFromTitle( $scriptTitle );
		$childFrame = $frame->newChild( array_slice( $args, 2 ), $scriptTitle );

		return Validator::cleanUp( $script->invoke( $scriptFunction, $childFrame ) );
	}

	/**
	 * Handles any exception that may have occurred during function invocation.
	 *
	 * @param Parser $parser The current MediaWiki parser
	 * @param ScriptException $exception The exception that has occurred
	 * @return string The error formatted as wikitext
	 */
	private function handleException( Parser $parser, ScriptException $exception ): string {
		$output = $parser->getOutput();
		$errors = $output->getExtensionData( self::Script_ERRORS_DATA_KEY );

		if ( $errors === null ) {
			// Add the tracking category only on the first error we encounter
			$parser->addTrackingCategory( 'script-error-category' );
			$errors = [];
		}

		// Allow other extensions to process any errors that may have occurred during script invocation
		$errors[] = $exception;
		$output->setExtensionData( self::Script_ERRORS_DATA_KEY, $errors );

		return Xml::tags(
			'strong',
			['class' => 'error'],
			Xml::span(
				wfMessage( 'script-error-prefix' )->parse() . ' ' . $exception->getMessage(),
				'script-error',
				['id' => 'mw-script-error-' . count( $errors )]
			)
		);
	}
}