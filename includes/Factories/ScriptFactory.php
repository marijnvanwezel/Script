<?php

namespace MediaWiki\Extension\FFI\Factories;

use MediaWiki\Extension\FFI\Exceptions\FFIException;
use MediaWiki\Extension\FFI\Exceptions\MissingEngineException;
use MediaWiki\Extension\FFI\Exceptions\NoSuchScriptException;
use MediaWiki\Extension\FFI\MediaWiki\ContentHandlers\ScriptContent;
use MediaWiki\Extension\FFI\Engines\Script;
use MediaWiki\Revision\RevisionRecord;
use MWException;
use Title;
use WikiPage;

class ScriptFactory {
	/**
	 * @var EngineFactory
	 */
	private $engineFactory;

	/**
	 * @param EngineFactory $engineFactory
	 */
	public function __construct( EngineFactory $engineFactory ) {
		$this->engineFactory = $engineFactory;
	}

	/**
	 * Constructs a script for the given Title.
	 *
	 * @param Title $title The page to get the script for
	 * @return Script
	 * @throws FFIException
	 */
	public function newFromTitle( Title $title ): Script {
		try {
			$wikiPage = WikiPage::factory( $title );
		} catch ( MWException $exception ) {
			throw new NoSuchScriptException( $title->getText() );
		}

		if ( $wikiPage === null ) {
			throw new NoSuchScriptException( $title->getText() );
		}

		$content = $wikiPage->getContent( RevisionRecord::RAW );

		if ( !$content instanceof ScriptContent ) {
			throw new NoSuchScriptException( $title->getText() );
		}

		$engine = $this->engineFactory->newFromTitle( $title );

		if ( $engine === null ) {
			throw new MissingEngineException( $title->getText() );
		}

		return new Script( $engine, $content->getText() );
	}
}
