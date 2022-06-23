<?php

/**
 * This file is loaded by MediaWiki\MediaWikiServices::getInstance() during the
 * bootstrapping of the dependency injection framework.
 *
 * @file
 */

use MediaWiki\Extension\Script\EngineStore;
use MediaWiki\Extension\Script\ScriptServices;
use MediaWiki\Extension\Script\ScriptFactory;
use MediaWiki\Extension\Script\MediaWiki\HookRunner;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;

return [
	"Script.EngineStore" => static function ( MediaWikiServices $services ): EngineStore {
		return new EngineStore(
			$services->getMainConfig()->get( 'ScriptEngines' ),
			ScriptServices::getHookRunner( $services )
		);
	},
	"Script.HookRunner" => static function ( MediaWikiServices $services ): HookRunner {
		return new HookRunner( $services->getHookContainer() );
	},
	"Script.Logger" => static function (): LoggerInterface {
		return LoggerFactory::getInstance( 'Script' );
	},
	"Script.ScriptFactory" => static function ( MediaWikiServices $services ): ScriptFactory {
		return new ScriptFactory( ScriptServices::getEngineStore( $services ) );
	}
];
