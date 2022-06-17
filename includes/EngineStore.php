<?php

namespace MediaWiki\Extension\FFI;

use MediaWiki\Extension\FFI\Engines\BaseEngine;
use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\Exceptions\NoSuchEngineException;
use MediaWiki\Extension\FFI\MediaWiki\HookRunner;
use Title;

class EngineStore {
	/**
	 * @var array Base engines defined through $wgFFIEngines
	 */
	private $baseEngines;

	/**
	 * @var HookRunner The global HookRunner singleton
	 */
	private $hookRunner;

	/**
	 * @var array[]|void Engines defined through either $wgFFIEngines or a hook.
	 *  May not be initialized until EngineStore::getEngines() has been called
	 */
	private $engines;

	/**
	 * @var BaseEngine[] Dictionary of already constructed Engine singletons
	 */
	private $instances = [];

	/**
	 * @param array $engines
	 * @param HookRunner $hookRunner
	 */
	public function __construct( array $engines, HookRunner $hookRunner ) {
		$this->baseEngines = $engines;
		$this->hookRunner = $hookRunner;
	}

	/**
	 * Returns the engine appropriate for the given Title.
	 *
	 * @param Title $title
	 * @param string|null $ext Will be set to the extension of the engine that was returned
	 * @return BaseEngine|null
	 * @throws InvalidEngineSpecificationException
	 */
	public function getByTitle( Title $title, ?string &$ext = null ): ?BaseEngine {
		if ( $title->getContentModel() !== CONTENT_MODEL_SCRIPT ) {
			return null;
		}

		return $this->getByName( $title->getText(), $ext );
	}

	/**
	 * Returns the engine appropriate for the given script name.
	 *
	 * @param string $name
	 * @param string|null $ext Will be set to the extension of the engine that was returned
	 * @return BaseEngine|null
	 * @throws InvalidEngineSpecificationException
	 */
	public function getByName( string $name, ?string &$ext = null ): ?BaseEngine {
		$parts = explode( '.', $name );
		$tryExt = end( $parts );

		try {
			$engine = $this->getByExt( $tryExt );
		} catch ( NoSuchEngineException $exception ) {
			return null;
		}

		$ext = $tryExt;
		return $engine;
	}

	/**
	 * Returns the engine that handles the given file extension. The language in which a file is written is
	 * identified by their file extension and is therefore also used as the identifier for the corresponding
	 * engine.
	 *
	 * @param string $ext The engine to construct
	 * @return BaseEngine
	 * @throws NoSuchEngineException When the requested engine does not exist
	 * @throws InvalidEngineSpecificationException When the specification of the requested engine is invalid
	 */
	public function getByExt( string $ext ): BaseEngine {
		if ( !isset( $this->instances[$ext] ) ) {
			$engines = $this->getEngines();

			if ( !isset( $engines[$ext] ) ) {
				throw new NoSuchEngineException( $ext );
			}

			$engineSpec = $engines[$ext];

			if ( isset( $engineSpec["disabled"] ) && $engineSpec["disabled"] !== false ) {
				// A disabled engine is handled exactly the same as an engine that does not exist
				throw new NoSuchEngineException( $ext );
			}

			if ( isset( $engineSpec["factory"] ) ) {
				// If the specification has a factory, delegate construction to that
				return call_user_func( $engineSpec["factory"], $engineSpec );
			}

			if ( !isset( $engineSpec["class"] ) ) {
				throw new InvalidEngineSpecificationException(
					$ext,
					'ffi-invalid-engine-specification-reason-missing-attribute',
					['class']
				);
			}

			$engineClass = $engineSpec["class"];

			if ( !class_exists( $engineClass ) ) {
				throw new InvalidEngineSpecificationException(
					$ext,
					'ffi-invalid-engine-specification-reason-nonexistent-class',
					[$engineClass]
				);
			}

			// Construct the engine class
			$this->instances[$ext] = new $engineClass( $engineSpec );
		}

		return $this->instances[$ext];
	}

	/**
	 * Returns the list of available engines.
	 *
	 * By default, the following engines are available:
	 *  - "py": Python engine
	 *
	 * A system administrator may add or remove engines through the $wgFFIEngines configuration parameter. Extensions
	 * may also implement additional engines through the "FFIGetEngines" hook.
	 *
	 * @return array
	 */
	public function getEngines(): array {
		if ( !isset( $this->engines ) ) {
			// We do not directly modify $this->baseEngines here, since we do not want to allow hooks to
			// overwrite or modify any settings a system administrator has made.
			$hookEngines = [];
			$this->hookRunner->onFFIGetEngines( $hookEngines );

			// Merge 2-dimensionally
			$this->engines = wfArrayPlus2d( $this->baseEngines, $hookEngines );
		}

		return $this->engines;
	}
}
