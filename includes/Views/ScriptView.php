<?php

namespace MediaWiki\Extension\FFI\Views;

use MediaWiki\Extension\FFI\Engines\Engine;
use SyntaxHighlight;
use Title;
use Xml;

/**
 * Implements the view for a script.
 */
class ScriptView implements View {
	/**
	 * @var Engine|null
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
	 * @param Title $title
	 * @param Engine|null $engine
	 */
	public function __construct( Title $title, ?Engine $engine ) {
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
			return Xml::tags( 'pre', ['class' => 'mw-code mw-script', 'dir' => 'ltr'], $rawText );
		}

		return $highlight;
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