<?php

namespace MediaWiki\Extension\FFI\MediaWiki\Hooks;

use MediaWiki\Extension\FFI\Engines\PythonStandalone\PythonStandaloneInterpreter;

/**
 * @stable for implementation
 */
interface FFIRegisterExternalPythonLibraries {
	/**
	 * This hook is called when external Python libraries are registered.
	 *
	 * @param PythonStandaloneInterpreter $pythonInterpreter The interpreter instance
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onFFIRegisterExternalPythonLibraries( PythonStandaloneInterpreter $pythonInterpreter );
}