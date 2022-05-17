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

namespace MediaWiki\Extension\FFI\Exceptions;

/**
 * Exception thrown when the requested engine does not exist.
 */
class NoSuchEngineException extends FFIException {
	/**
	 * @param string $ext The identifier of the engine that could not be found
	 */
	public function __construct( string $ext ) {
		parent::__construct(
			'ffi-no-such-engine-exception',
			[$ext],
			"The engine '$ext' does not exist."
		);
	}
}
