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

namespace MediaWiki\Extension\FFI;

use MediaWiki\Extension\FFI\Engines\Engine;
use PPFrame;

/**
 * Represents a foreign script that can be executed.
 *
 * @note Basically a wrapper around a script and an engine that can execute that script.
 */
class Script {
	/**
	 * @var Engine Reference to the engine that can execute this script
	 */
	private $engine;

	/**
	 * @var string The source code of this script
	 */
	private $source;

	/**
	 * @param Engine $engine Reference to the engine used to execute this script
	 * @param string $source The source code of this script
	 */
	public function __construct( Engine $engine, string $source ) {
		$this->engine = $engine;
		$this->source = $source;
	}

	/**
	 * Invokes the specified function in this script.
	 *
	 * @param string $name The name of the function to call
	 * @param PPFrame $frame The frame to pass to the function
	 * @return string The result of the function invocation
	 */
	public function invoke( string $name, PPFrame $frame ): string {
		return $this->engine->executeScript( $this->source, $name, $frame );
	}
}
