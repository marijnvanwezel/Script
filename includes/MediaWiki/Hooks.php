<?php

namespace MediaWiki\Extension\FFI\MediaWiki;

use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\Factories\EngineFactory;
use MediaWiki\Extension\FFI\FFIServices;
use MediaWiki\Hook\EditFilterMergedContentHook;
use MediaWiki\Hook\EditPageBeforeEditButtonsHook;
use Title;

/**
 * Hook handler for any hooks that FFI implements.
 */
class Hooks implements EditFilterMergedContentHook, EditPageBeforeEditButtonsHook {
	/**
	 * @var EngineFactory
	 */
	private $engineFactory;

	/**
	 * Callback function called after extension.json has been processed.
	 *
	 * @return void
	 */
	public static function onRegistration(): void {
		define( 'CONTENT_MODEL_SCRIPT', 'script' );
	}

	/**
	 * Hook provided for extensions to extend CodeEditor by supporting additional languages.
	 *
	 * @param Title $title
	 * @param string $lang
	 * @param string $model
	 * @param string $format
	 * @return void
	 * @throws InvalidEngineSpecificationException
	 */
	public static function onCodeEditorGetPageLanguage( $title, &$lang, $model, $format ): bool {
		$engine = FFIServices::getEngineFactory()->newFromTitle( $title );

		if ( $engine !== null ) {
			$lang = $engine->getCodeEditorName() ?? $lang;

			return false;
		}

		return true;
	}

	/**
	 * @param EngineFactory $engineFactory
	 */
	public function __construct( EngineFactory $engineFactory ) {
		$this->engineFactory = $engineFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onEditFilterMergedContent( $context, $content, $status, $summary, $user, $minoredit ): void {
		if ( $content->getContentHandler()->getModelID() !== CONTENT_MODEL_SCRIPT ) {
			// Not our concern
			return;
		}

		try {
			$engine = $this->engineFactory->newFromTitle( $context->getTitle() );
		} catch ( InvalidEngineSpecificationException $e ) {
			$engine = null;
		}

		if ( $engine === null ) {
			// Special error for when no engine is configured
			$status->fatal( 'ffi-no-engine' );

			return;
		}

		// TODO
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
}