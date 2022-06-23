<?php

namespace MediaWiki\Extension\Script;

use MediaWiki\Extension\Script\EngineStore;
use MediaWiki\Extension\Script\Exceptions\ScriptException;
use MediaWiki\Extension\Script\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\Script\Exceptions\MissingEngineException;
use MediaWiki\Extension\Script\Exceptions\NoSuchScriptException;
use MediaWiki\Extension\Script\MediaWiki\ContentHandlers\ScriptContent;
use MediaWiki\Extension\Script\Engines\Script;
use MediaWiki\Revision\RevisionRecord;
use MWException;
use Title;
use WikiPage;

class ScriptFactory {
	/**
	 * @var EngineStore
	 */
	private $engineFactory;

	/**
	 * @param EngineStore $engineFactory
	 */
	public function __construct( EngineStore $engineFactory ) {
		$this->engineFactory = $engineFactory;
	}

	/**
	 * Constructs a script for the given Title.
	 *
	 * @param Title $title The page to get the script for
	 * @param string|null $ext Will be set to the extension of the engine that is used
	 * @return Script
	 * @throws InvalidEngineSpecificationException
	 * @throws MissingEngineException
	 * @throws NoSuchScriptException
	 */
	public function newFromTitle( Title $title, ?string &$ext = null ): Script {
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

		$engine = $this->engineFactory->getByTitle( $title, $ext );

		if ( $engine === null ) {
			throw new MissingEngineException( $title->getText() );
		}

		return new Script( $engine, $content->getText() );
	}
}
