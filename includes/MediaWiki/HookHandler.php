<?php

namespace MediaWiki\Extension\FFI\MediaWiki;

use Exception;
use MediaWiki\Extension\FFI\Exceptions\FFIException;
use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\EngineStore;
use MediaWiki\Extension\FFI\ScriptFactory;
use MediaWiki\Extension\FFI\MediaWiki\ContentHandlers\ScriptContent;
use MediaWiki\Extension\FFI\MediaWiki\ParserFunctions\ScriptParserFunction;
use MediaWiki\Extension\FFI\Utils;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Hook\EditPageBeforeEditButtonsHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Page\Hook\ArticleViewHeaderHook;
use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;
use MWException;
use Parser;
use Status;

/**
 * Hook handler for any modern hooks that FFI implements.
 */
class HookHandler implements
	ArticleViewHeaderHook,
	ContentHandlerDefaultModelForHook,
	EditFilterMergedContentHook,
	EditPageBeforeEditButtonsHook,
	ParserFirstCallInitHook {
	/**
	 * @var EngineStore
	 */
	private $engineStore;

	/**
	 * @var ScriptFactory
	 */
	private $scriptFactory;

	/**
	 * @param EngineStore $engineStore
	 * @param ScriptFactory $scriptFactory
	 */
	public function __construct( EngineStore $engineStore, ScriptFactory $scriptFactory ) {
		$this->engineStore = $engineStore;
		$this->scriptFactory = $scriptFactory;
	}

	/**
	 * @inheritDoc
	 * @throws InvalidEngineSpecificationException
	 */
	public function onArticleViewHeader( $article, &$pcache, &$outputDone ): void {
		if ( !Utils::isDocPage( $article->getTitle(), $forScript ) ) {
			return;
		}

		$engine = $this->engineStore->getByTitle( $forScript );
		$header = wfMessage(
			'ffi-doc-page-header',
			$engine->getHumanName(),
			$forScript->getFullText()
		)->parseAsBlock();

		$article->getContext()->getOutput()->addHTML( $header );
	}

	/**
	 * @inheritDoc
	 * @throws InvalidEngineSpecificationException
	 */
	public function onContentHandlerDefaultModelFor( $title, &$model ): void {
		if ( $title->inNamespace( NS_SCRIPT ) && !Utils::isDocPage( $title ) ) {
			$model = CONTENT_MODEL_SCRIPT;
		}
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

		$engine = $this->engineStore->getByTitle( $context->getTitle(), $ext );

		if ( $engine === null ) {
			// Special error for when no engine is configured
			$status->fatal( 'ffi-no-engine-error' );
		} else {
			// Let the engine validate the source code
			try {
				$engine->validateSource( $content->getText(), $status );
			} catch ( Exception $exception ) {
				// The validation failed miserably. This does not mean the script is invalid, it just means that the
				// validation of the script could not be completed due to uncaught errors. In this case, the engine
				// is in a broken state, and we should reset it.
				$status->fatal( $exception->getMessage() );
			}
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