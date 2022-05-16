<?php

namespace MediaWiki\Extension\Pybunto;

class Hooks {
	/**
	 * Called right after the extension is initialized and the extension.json is parsed.
	 *
	 * @return void
	 */
	public static function onRegistration(): void {
		global $wgScribuntoEngineConf;

		$wgScribuntoEngineConf['pythonstandalone'] = [
			'class' => 'MediaWiki\\Extension\\Pybunto\\RestrictedPythonEngine'
		];
	}
}
