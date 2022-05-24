<?php

namespace MediaWiki\Extension\FFI;

use MediaWiki\Extension\FFI\Factories\EngineFactory;
use MediaWiki\Extension\FFI\Factories\ScriptFactory;
use MediaWiki\Extension\FFI\MediaWiki\HookRunner;
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

	public static function getHookRunner( ?ServiceContainer $services = null ): HookRunner {
		return self::getService( "HookRunner", $services );
	}

	public static function getScriptFactory( ?ServiceContainer $services = null ): ?ScriptFactory {
		return self::getService( "ScriptFactory", $services );
	}

	private static function getService( string $service, ?ServiceContainer $services ) {
		return ( $services ?: MediaWikiServices::getInstance() )->getService( "FFI.$service" );
	}
}
