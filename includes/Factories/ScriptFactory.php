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

namespace MediaWiki\Extension\FFI\Factories;

use MediaWiki\Extension\FFI\Engines\Engine;
use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\Exceptions\NoSuchEngineException;
use MediaWiki\Extension\FFI\MediaWiki\ContentHandlers\ScriptContent;
use MediaWiki\Extension\FFI\Script;
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
	 * Constructs a script for the given Title. May return NULL for titles
	 * that do not represent a proper script.
	 *
	 * @param Title $title The page to get the script for
	 * @return Script|null
	 * @throws InvalidEngineSpecificationException
	 */
	public function newFromTitle( Title $title ): ?Script {
		try {
			$wikiPage = WikiPage::factory( $title );
		} catch ( MWException $exception ) {
			return null;
		}

		$content = $wikiPage->getContent( RevisionRecord::RAW );

		if ( !$content instanceof ScriptContent ) {
			return null;
		}

		$engine = $this->engineFactory->newFromTitle( $title );

		if ( $engine === null ) {
			return null;
		}

		return new Script( $engine, $content->getText() );
	}
}
