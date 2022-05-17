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

use MediaWiki\MediaWikiServices;
use Wikimedia\Services\ServiceContainer;

/**
 * Getter for all FFI services. This class reduces the risk of mistyping
 * a service name and serves as the interface for retrieving services for
 * FFI.
 *
 * @note Program logic should use dependency injection instead of this class wherever
 * possible.
 *
 * @note This class should only contain static methods.
 */
final class FFIServices {
	/**
	 * Disable the construction of this class by making the constructor private.
	 */
	private function __construct() {
	}

	public static function getEngineFactory( ?ServiceContainer $services = null ): EngineFactory {
		return self::getService( "EngineFactory", $services );
	}

	private static function getService( string $service, ?ServiceContainer $services ) {
		return ( $services ?: MediaWikiServices::getInstance() )->getService( "FFI.$service" );
	}
}
