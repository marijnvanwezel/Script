<?php

namespace MediaWiki\Extension\Script\MediaWiki;

use MediaWiki\Extension\Script\Engines\PythonStandalone\EngineProcess;
use MediaWiki\Extension\Script\Engines\PythonStandalone\PythonStandaloneEngine;
use MediaWiki\Extension\Script\MediaWiki\Hooks\ScriptGetEngines;
use MediaWiki\Extension\Script\MediaWiki\Hooks\ScriptRegisterExternalPythonLibraries;
use MediaWiki\HookContainer\HookContainer;

/**
 * Hook runner for all Script hooks.
 */
class HookRunner implements ScriptGetEngines, ScriptRegisterExternalPythonLibraries {
	/**
	 * @var HookContainer
	 */
	private $container;

	/**
	 * @param HookContainer $container
	 */
	public function __construct( HookContainer $container ) {
		$this->container = $container;
	}

	/**
	 * @inheritDoc
	 */
	public function onScriptGetEngines( array &$engines ) {
		$this->container->run( 'ScriptGetEngines', [&$engines] );
	}

	/**
	 * @inheritDoc
	 */
	public function onScriptRegisterExternalPythonLibraries( array $libraries ) {
		$this->container->run( 'ScriptRegisterExternalPythonLibraries', [$libraries] );
	}
}