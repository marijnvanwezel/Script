<?php

namespace MediaWiki\Extension\Script;

use MediaWiki\Extension\Script\EngineStore;
use MediaWiki\Extension\Script\ScriptFactory;
use MediaWiki\Extension\Script\MediaWiki\HookRunner;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;
use Wikimedia\Services\ServiceContainer;

/**
 * Getter for all Script services. This class reduces the risk of mistyping
 * a service name and serves as the interface for retrieving services for
 * Script.
 *
 * @note Program logic should use dependency injection instead of this class wherever
 * possible.
 *
 * @note This class should only contain static methods.
 */
final class ScriptServices {
	/**
	 * Disable the construction of this class by making the constructor private.
	 */
	private function __construct() {
	}

	public static function getEngineStore( ?ServiceContainer $services = null ): EngineStore {
		return self::getService( "EngineStore", $services );
	}

	public static function getHookRunner( ?ServiceContainer $services = null ): HookRunner {
		return self::getService( "HookRunner", $services );
	}

	public static function getLogger( ?ServiceContainer $services = null ): LoggerInterface {
		return self::getService( "Logger", $services );
	}

	public static function getScriptFactory( ?ServiceContainer $services = null ): ?ScriptFactory {
		return self::getService( "ScriptFactory", $services );
	}

	private static function getService( string $service, ?ServiceContainer $services ) {
		return ( $services ?: MediaWikiServices::getInstance() )->getService( "Script.$service" );
	}
}
