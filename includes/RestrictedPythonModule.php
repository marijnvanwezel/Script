<?php

namespace MediaWiki\Extension\Pybunto;

use MediaWiki\Extension\Scribunto\ScribuntoModuleBase;
use Status;

class RestrictedPythonModule extends ScribuntoModuleBase {
	/**
	 * @inheritDoc
	 */
	public function validate(): Status {
		// TODO: Implement validate() method.
		return Status::newGood();
	}

	/**
	 * @inheritDoc
	 */
	public function invoke( $name, $frame ) {
		$ret = $this->engine->executeModule( $name, $frame );

		if ( !isset( $ret ) ) {
			throw $this->engine->newException(
				'scribunto-common-nosuchfunction', [ 'args' => [ $name ] ]
			);
		}
		if ( !$this->engine->getInterpreter()->isLuaFunction( $ret ) ) {
			throw $this->engine->newException(
				'scribunto-common-notafunction', [ 'args' => [ $name ] ]
			);
		}

		$result = $this->engine->executeFunctionChunk( $ret, $frame );

		return $result[0] ?? null;
	}
}
