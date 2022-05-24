<?php

namespace MediaWiki\Extension\FFI\MediaWiki\Hooks;

/**
 * @stable for implementation
 */
interface FFIGetEngines {
	/**
	 * This hook is called when the list of available engines is retrieved. It can be used to add additional engines.
	 *
	 * @param array $engines The list of available engines
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onFFIGetEngines( array &$engines );
}