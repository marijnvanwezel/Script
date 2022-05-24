<?php

namespace MediaWiki\Extension\FFI\Factories;

use Config;
use MediaWiki\Extension\FFI\Engines\Engine;
use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\Exceptions\NoSuchEngineException;
use MediaWiki\Extension\FFI\MediaWiki\HookRunner;
use Title;

class EngineFactory {
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var HookRunner
	 */
	private $hookRunner;

	/**
	 * @var array[]|null
	 */
	private $engines;

	/**
	 * @param Config $config
	 * @param HookRunner $hookRunner
	 */
	public function __construct( Config $config, HookRunner $hookRunner ) {
		$this->config = $config;
		$this->hookRunner = $hookRunner;
	}

	/**
	 * Returns the engine appropriate for the given Title.
	 *
	 * @param Title $title
	 * @return Engine|null
	 * @throws InvalidEngineSpecificationException
	 */
	public function newFromTitle( Title $title ): ?Engine {
		if ( !$title->inNamespace( NS_SCRIPT ) ) {
			return null;
		}

		return $this->newFromName( $title->getBaseText() );
	}

	/**
	 * Returns the engine appropriate for the given script name.
	 *
	 * @param string $name
	 * @return Engine|null
	 * @throws InvalidEngineSpecificationException
	 */
	public function newFromName( string $name ): ?Engine {
		$parts = explode( '.', $name );
		$ext = end( $parts );

		try {
			return $this->newFromExt( $ext );
		} catch ( NoSuchEngineException $exception ) {
			return null;
		}
	}

	/**
	 * Returns the engine that handles the given file extension. The language in which a file is written is
	 * identified by their file extension and is therefore also used as the identifier for the corresponding
	 * engine.
	 *
	 * @param string $ext The engine to construct
	 * @return Engine
	 * @throws NoSuchEngineException When the requested engine does not exist
	 * @throws InvalidEngineSpecificationException When the specification of the requested engine is invalid
	 */
	public function newFromExt( string $ext ): Engine {
		$engines = $this->getEngines();

		if ( !isset( $engines[$ext] ) ) {
			throw new NoSuchEngineException( $ext );
		}

		$engineSpec = $engines[$ext];

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
		return new $engineClass( $engineSpec );
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
			$this->engines = $this->config->get( 'FFIEngines' );
			$this->hookRunner->onFFIGetEngines( $this->engines );
		}

		return $this->engines;
	}
}
