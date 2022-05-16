<?php

namespace MediaWiki\Extension\Pybunto;

use MediaWiki\Extension\Scribunto\ScribuntoEngineBase;
use PPFrame;

class RestrictedPythonEngine extends ScribuntoEngineBase {
	/**
	 * @inheritDoc
	 */
	public function runConsole( array $params ) {
		// TODO: Implement runConsole() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getSoftwareInfo( array &$software ) {
		// TODO: Implement getSoftwareInfo() method.
	}

	public function executeModule( string $functionName, PPFrame $frame ) {

	}

	/**
	 * @inheritDoc
	 */
	protected function newModule( $text, $chunkName ) {
		return new RestrictedPythonModule( $this, $text, $chunkName );
	}
}
