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
use Title;

class EngineFactory {
	/**
	 * @var array The array of available engines
	 */
	private $engines;

	/**
	 * @param array $engines The array of available engines (from $wgFFIEngines)
	 */
	public function __construct( array $engines ) {
		$this->engines = $engines;
	}

	/**
	 * Returns the engine appropriate for the given Title.
	 *
	 * @param Title $title
	 * @return Engine|null
	 * @throws InvalidEngineSpecificationException
	 */
	public function newFromTitle( Title $title ): ?Engine {
		if ( !$title->inNamespace( NS_SCRIPT ) ) {
			return null;
		}

		$titleParts = explode( '.', $title->getBaseText() );
		$titleExt = end( $titleParts );

		try {
			return $this->newFromExt( $titleExt );
		} catch ( NoSuchEngineException $exception ) {
			return null;
		}
	}

	/**
	 * Returns the engine that handles the given file extension. The language in which a file is written is
	 * identified by their file extension and is therefore also used as the identifier for the corresponding
	 * engine.
	 *
	 * By default, the following engines are available:
	 *  - "py": Python engine
	 *
	 * A system administrator may add or remove engines through the $wgFFIEngines configuration parameter.
	 *
	 * @param string $ext The engine to construct
	 * @return Engine
	 * @throws NoSuchEngineException When the requested engine does not exist
	 * @throws InvalidEngineSpecificationException When the specification of the requested engine is invalid
	 */
	public function newFromExt( string $ext ): Engine {
		if ( !isset( $this->engines[$ext] ) ) {
			throw new NoSuchEngineException( $ext );
		}

		$engineSpec = $this->engines[$ext];

		if ( isset( $engineSpec["factory"] ) ) {
			// If the specification has a factory, delegate construction to that
			return call_user_func( $engineSpec["factory"], $engineSpec );
		}

		if ( !isset( $engineSpec["class"] ) ) {
			throw new InvalidEngineSpecificationException(
				$ext,
				'ffi-invalid-engine-specification-reason-missing-attribute',
				['class'],
				"missing 'class' attribute"
			);
		}

		$engineClass = $engineSpec["class"];

		if ( !class_exists( $engineClass ) ) {
			throw new InvalidEngineSpecificationException(
				$ext,
				'ffi-invalid-engine-specification-reason-nonexistent-class',
				[$engineClass],
				"the class '{$engineClass} does not exist"
			);
		}

		// Construct the engine class
		return new $engineClass( $engineSpec );
	}
}
