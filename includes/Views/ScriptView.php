<?php

namespace MediaWiki\Extension\Script\Views;

use MediaWiki\Extension\Script\Engines\BaseEngine;
use MediaWiki\Extension\Script\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\Script\Utils;
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
			$text = wfMessage( 'script-missing-engine-indicator' )->parse();
			return ['script-engine' => Xml::span( $text, 'mw-script-missing-engine-indicator' )];
		}

		$name = $this->engine->getHumanName();
		$content = Xml::span( $name . ' ' . $this->engine->getVersion(), 'mw-script-engine-name-indicator' );
		$helpUrl = $this->engine->getHelpUrl();

		if ( $helpUrl !== null ) {
			$attribs = [
				'href' => $helpUrl,
				'class' => 'mw-script-engine-help-link-indicator',
				'target' => '_blank'
			];

			$content = Xml::tags( 'a', $attribs, $content );
		}

		$logo = $this->engine->getLogo();

		if ( $logo !== null ) {
			$attribs = [
				'src' => $logo,
				'alt' => wfMessage( 'script-engine-logo-indicator-alt-text', $name )->parse(),
				'class' => 'mw-script-engine-logo-indicator'
			];

			$content = Xml::element( 'img' , $attribs, '', true ) . ' ' . $content;
		}

		return ['script-engine' => $content];
	}

	/**
	 * @inheritDoc
	 */
	public function getModules(): array {
		return array_merge( ['ext.script.ui'], $this->modules );
	}

	/**
	 * Returns the HTML of the doc page.
	 *
	 * @return string
	 */
	public function getHeaderHtml(): string {
		if ( $this->engine === null ) {
			return wfMessage( 'script-missing-engine-header' )->parse();
		}

		$docPage = Utils::getDocPage( $this->title );

		return $docPage->exists() ?
			wfMessage( 'script-doc-page-transclusion', $docPage->getFullText() )->parse() :
			wfMessage( 'script-doc-page-does-not-exist', $this->engine->getHumanName(), $docPage->getFullText() )->parse();
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