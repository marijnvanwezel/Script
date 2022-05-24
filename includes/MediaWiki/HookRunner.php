<?php

namespace MediaWiki\Extension\FFI\MediaWiki;

use MediaWiki\Extension\FFI\MediaWiki\Hooks\FFIGetEngines;
use MediaWiki\HookContainer\HookContainer;

/**
 * Hook runner for all FFI hooks.
 */
class HookRunner implements FFIGetEngines {
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
	public function onFFIGetEngines( array &$engines ) {
		$this->container->run( 'FFIGetEngines', [&$engines] );
	}
}