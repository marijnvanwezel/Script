<?php

/**
 * This file is loaded by MediaWiki\MediaWikiServices::getInstance() during the
 * bootstrapping of the dependency injection framework.
 *
 * @file
 */

use MediaWiki\Extension\FFI\Factories\EngineFactory;
use MediaWiki\Extension\FFI\FFIServices;
use MediaWiki\Extension\FFI\Factories\ScriptFactory;
use MediaWiki\Extension\FFI\MediaWiki\HookRunner;
use MediaWiki\MediaWikiServices;

return [
	"FFI.EngineFactory" => static function ( MediaWikiServices $services ): EngineFactory {
		return new EngineFactory( $services->getMainConfig(), FFIServices::getHookRunner( $services ) );
	},
	"FFI.HookRunner" => static function ( MediaWikiServices $services ): HookRunner {
		return new HookRunner( $services->getHookContainer() );
	},
	"FFI.ScriptFactory" => static function ( MediaWikiServices $services ): ScriptFactory {
		return new ScriptFactory( FFIServices::getEngineFactory( $services ) );
	}
];
