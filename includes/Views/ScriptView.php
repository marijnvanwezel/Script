<?php

namespace MediaWiki\Extension\FFI\Views;

use MediaWiki\Extension\FFI\Engines\BaseEngine;
use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\Utils;
use SyntaxHighlight;
use Title;
use Xml;

/**
 * Implements the view for a script.
 */
class ScriptView implements View {
	/**
	 * @var BaseEngine|null
	 */
	private $engine;

	/**
	 * @var Title
	 */
	private $title;

	/**
	 * @var string[] Array of modules to load
	 */
	private $modules = [];

	/**
	 * @param Title $title The title to the script page
	 * @param BaseEngine|null $engine
	 */
	public function __construct( Title $title, ?BaseEngine $engine ) {
		$this->title = $title;
		$this->engine = $engine;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitleHTML(): string {
		return htmlspecialchars( $this->title->getFullText() );
	}

	/**
	 * @inheritDoc
	 */
	public function getContentHTML( string $rawText ): string {
		$language = $this->engine !== null ? $this->engine->getGeSHiName() : null;
		$highlight = $this->highlightCode( $rawText, $language );

		if ( $highlight === null ) {
			// Fallback to default "<pre>" styling
			$highlight = Xml::tags( 'pre', ['class' => 'mw-code mw-script', 'dir' => 'ltr'], $rawText );
		}

		$docPage = $this->getHeaderHtml();

		return $docPage . $highlight;
	}

	/**
	 * @inheritDoc
	 */
	public function getIndicators(): ?array {
		if ( $this->engine === null ) {
			$text = wfMessage( 'ffi-missing-engine-indicator' )->parse();
			return ['ffi-engine' => Xml::span( $text, 'mw-ffi-missing-engine-indicator' )];
		}

		$name = $this->engine->getHumanName();
		$content = Xml::span( $name . ' ' . $this->engine->getVersion(), 'mw-ffi-engine-name-indicator' );
		$helpUrl = $this->engine->getHelpUrl();

		if ( $helpUrl !== null ) {
			$attribs = [
				'href' => $helpUrl,
				'class' => 'mw-ffi-engine-help-link-indicator',
				'target' => '_blank'
			];

			$content = Xml::tags( 'a', $attribs, $content );
		}

		$logo = $this->engine->getLogo();

		if ( $logo !== null ) {
			$attribs = [
				'src' => $logo,
				'alt' => wfMessage( 'ffi-engine-logo-indicator-alt-text', $name )->parse(),
				'class' => 'mw-ffi-engine-logo-indicator'
			];

			$content = Xml::element( 'img' , $attribs, '', true ) . ' ' . $content;
		}

		return ['ffi-engine' => $content];
	}

	/**
	 * @inheritDoc
	 */
	public function getModules(): array {
		return array_merge( ['ext.ffi.ui'], $this->modules );
	}

	/**
	 * Returns the HTML of the doc page.
	 *
	 * @return string
	 */
	public function getHeaderHtml(): string {
		if ( $this->engine === null ) {
			return wfMessage( 'ffi-missing-engine-header' )->parse();
		}

		$docPage = Utils::getDocPage( $this->title );

		return $docPage->exists() ?
			wfMessage( 'ffi-doc-page-transclusion', $docPage->getFullText() )->parse() :
			wfMessage( 'ffi-doc-page-does-not-exist', $this->engine->getHumanName(), $docPage->getFullText() )->parse();
	}

	/**
	 * Highlight the given code.
	 *
	 * @param string $code
	 * @param string|null $language
	 * @return string|null
	 */
	private function highlightCode( string $code, ?string $language ): ?string {
		if ( !class_exists( SyntaxHighlight::class ) ) {
			return null;
		}

		$status = SyntaxHighlight::highlight( $code, $language );

		if ( !$status->isGood() ) {
			return null;
		}

		// Add the Pygments module
		$this->modules[] = 'ext.pygments';

		return $status->getValue();
	}
}