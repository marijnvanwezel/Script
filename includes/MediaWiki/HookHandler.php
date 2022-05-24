<?php

namespace MediaWiki\Extension\FFI\MediaWiki;

use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\Factories\EngineFactory;
use MediaWiki\Extension\FFI\Factories\ScriptFactory;
use MediaWiki\Extension\FFI\MediaWiki\ContentHandlers\ScriptContent;
use MediaWiki\Extension\FFI\MediaWiki\ParserFunctions\ScriptParserFunction;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Hook\EditPageBeforeEditButtonsHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MWException;
use Parser;

/**
 * Hook handler for any modern hooks that FFI implements.
 */
class HookHandler implements EditFilterMergedContentHook, EditPageBeforeEditButtonsHook, ParserFirstCallInitHook {
	/**
	 * @var EngineFactory
	 */
	private $engineFactory;

	/**
	 * @var ScriptFactory
	 */
	private $scriptFactory;

	/**
	 * @param EngineFactory $engineFactory
	 * @param ScriptFactory $scriptFactory
	 */
	public function __construct( EngineFactory $engineFactory, ScriptFactory $scriptFactory ) {
		$this->engineFactory = $engineFactory;
		$this->scriptFactory = $scriptFactory;
	}

	/**
	 * @inheritDoc
	 * @throws InvalidEngineSpecificationException
	 */
	public function onEditFilterMergedContent( $context, $content, $status, $summary, $user, $minoredit ): void {
		if ( !$content instanceof ScriptContent ) {
			// Not our concern
			return;
		}

		$engine = $this->engineFactory->newFromTitle( $context->getTitle() );

		if ( $engine === null ) {
			// Special error for when no engine is configured
			$status->fatal( 'ffi-no-engine-error' );
		} else {
			// Let the engine validate the source code
			$engine->validateSource( $content->getText(), $status );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onEditPageBeforeEditButtons( $editpage, &$buttons, &$tabindex ): void {
		if ( $editpage->getTitle()->hasContentModel( CONTENT_MODEL_SCRIPT ) ) {
			// Disable previewing by hiding the button
			unset( $buttons['preview'] );
		}
	}

	/**
	 * @inheritDoc
	 * @throws MWException
	 */
	public function onParserFirstCallInit( $parser ): void {
		$parser->setFunctionHook(
			'script',
			[new ScriptParserFunction( $this->scriptFactory ), 'execute'],
			Parser::SFH_OBJECT_ARGS
		);
	}
}