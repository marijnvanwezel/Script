<?php

namespace MediaWiki\Extension\Script\MediaWiki;

use MediaWiki\Extension\Script\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\Script\ScriptServices;
use Title;

/**
 * Handler for any legacy hooks that do not (yet) have a HookRunner.
 *
 * @note This class should only contain static methods.
 */
final class LegacyHooks {
	/**
	 * Disable construction by making the constructor private.
	 */
	private function __construct() {
	}

	/**
	 * Callback function called after extension.json has been processed.
	 *
	 * @return void
	 */
	public static function onRegistration(): void {
		define( 'CONTENT_MODEL_SCRIPT', 'script' );
	}

	/**
	 * Hook provided for extensions to extend CodeEditor by supporting additional languages.
	 *
	 * @param Title $title
	 * @param string $lang
	 * @param string $model
	 * @param string $format
	 * @return void
	 * @throws InvalidEngineSpecificationException
	 */
	public static function onCodeEditorGetPageLanguage( $title, &$lang, $model, $format ): bool {
		if ( !$GLOBALS['wgScriptEnableCodeEditor'] ) {
			return true;
		}

		$engine = ScriptServices::getEngineStore()->getByTitle( $title );

		if ( $engine === null ) {
			return true;
		}

		$engineLang = $engine->getCodeEditorName();

		if ( $engineLang === null ) {
			return true;
		}

		$lang = $engineLang;

		return false;
	}
}