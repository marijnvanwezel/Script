<?php

/*
 * FFI MediaWiki extension
 * Copyright (C) 2021  Marijn van Wezel
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

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
			return "<pre class='mw-code mw-script' dir='ltr'>" . htmlspecialchars( $rawText ) . "</pre>";
		}

		return $highlight;
	}

	/**
	 * @inheritDoc
	 */
	public function getIndicators(): ?array {
		if ( $this->engine === null ) {
			$text = wfMessage( 'ffi-missing-engine-indicator' )->parse();
			return [Xml::span( $text, 'mw-ffi-missing-engine-indicator' )];
		}

		return [Xml::span( $this->engine->getHumanName(), 'mw-ffi-engine-type-indicator' )];
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