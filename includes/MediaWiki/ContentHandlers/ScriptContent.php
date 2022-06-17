<?php

namespace MediaWiki\Extension\FFI\MediaWiki\ContentHandlers;

use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\FFIServices;
use MediaWiki\Extension\FFI\Views\ScriptView;
use ParserOptions;
use ParserOutput;
use TextContent;
use Title;

class ScriptContent extends TextContent {
	/**
	 * @inheritDoc
	 */
	public function __construct( $text, $model_id = CONTENT_MODEL_SCRIPT ) {
		parent::__construct( $text, $model_id );
	}

	/**
	 * @inheritDoc
	 * @throws InvalidEngineSpecificationException
	 */
	protected function fillParserOutput(
		Title $title,
		$revId,
		ParserOptions $options,
		$generateHtml,
		ParserOutput &$output
	) {
		if ( !$generateHtml ) {
			return;
		}

		$engine = FFIServices::getEngineStore()->getByTitle( $title );
		$view = new ScriptView( $title, $engine );

		foreach ( $view->getIndicators() as $id => $content ) {
			$output->setIndicator( $id, $content );
		}

		$output->setDisplayTitle( $view->getTitleHTML() );
		$output->setText( $view->getContentHTML( $this->getText() ) );
		$output->addModules( $view->getModules() );
	}
}