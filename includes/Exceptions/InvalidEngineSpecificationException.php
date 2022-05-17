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
 * Exception throws when the specification of the engine we're trying to construct is invalid.
 */
class InvalidEngineSpecificationException extends FFIException {
	/**
	 * @param string $ext The identifier of the engine for which the specification is invalid
	 * @param string $reasonName The name of the i18n message that specifies the reason
	 * @param string[] $reasonArgs Any arguments that need to be passed to $reasonName
	 * @param string $reasonFallback A fallback message to use for the reason if the message cache is not available
	 */
	public function __construct( string $ext, string $reasonName, array $reasonArgs, string $reasonFallback ) {
		$reason = $this->msg( $reasonName, $reasonFallback, $reasonArgs );

		parent::__construct(
			'ffi-invalid-engine-specification-exception',
			[$ext, $reason],
			"The specification for the engine '$ext' is invalid ($reason)."
		);
	}
}
