<?php

/**
 * This file is loaded by MediaWiki\MediaWikiServices::getInstance() during the
 * bootstrapping of the dependency injection framework.
 *
 * @file
 */

use MediaWiki\Extension\FFI\EngineStore;
use MediaWiki\Extension\FFI\FFIServices;
use MediaWiki\Extension\FFI\ScriptFactory;
use MediaWiki\Extension\FFI\MediaWiki\HookRunner;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;

return [
	"FFI.EngineStore" => static function ( MediaWikiServices $services ): EngineStore {
		return new EngineStore(
			$services->getMainConfig()->get( 'FFIEngines' ),
			FFIServices::getHookRunner( $services )
		);
	},
	"FFI.HookRunner" => static function ( MediaWikiServices $services ): HookRunner {
		return new HookRunner( $services->getHookContainer() );
	},
	"FFI.Logger" => static function (): LoggerInterface {
		return LoggerFactory::getInstance( 'FFI' );
	},
	"FFI.ScriptFactory" => static function ( MediaWikiServices $services ): ScriptFactory {
		return new ScriptFactory( FFIServices::getEngineStore( $services ) );
	}
];
