<?php

namespace MediaWiki\Extension\Script\MediaWiki\ContentHandlers;

use MediaWiki\Extension\Script\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\Script\ScriptServices;
use TextContentHandler;
use Title;

class ScriptContentHandler extends TextContentHandler {
	/**
	 * @inheritDoc
	 */
	public function __construct( $modelId = CONTENT_MODEL_SCRIPT, $formats = [CONTENT_FORMAT_TEXT] ) {
		parent::__construct( $modelId, $formats );
	}

	/**
	 * @inheritDoc
	 * @throws InvalidEngineSpecificationException
	 */
	public function canBeUsedOn( Title $title ): bool {
		if ( $title->getNamespace() !== NS_SCRIPT ) {
			// Disable "script" model outside the "Script" namespace
			return false;
		}

		if ( ScriptServices::getEngineStore()->getByTitle( $title ) === null ) {
			// Disable the "script" module for pages with invalid file extensions
			return false;
		}

		return parent::canBeUsedOn( $title );
	}

	/**
	 * @inheritDoc
	 */
	protected function getContentClass(): string {
		return ScriptContent::class;
	}
}