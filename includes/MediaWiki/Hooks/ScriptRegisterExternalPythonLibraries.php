<?php

namespace MediaWiki\Extension\Script\MediaWiki\Hooks;

/**
 * @stable for implementation
 */
interface ScriptRegisterExternalPythonLibraries {
	/**
	 * This hook is called when external Python libraries are registered.
	 *
	 * @param array $libraries The specification of the libraries to register
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onScriptRegisterExternalPythonLibraries( array $libraries );
}