<?php

namespace MediaWiki\Extension\FFI\Engines;

/**
 * Base implementation of an FFIEngine that provides some convenience functions that can be
 * used by implementations.
 */
abstract class EngineBase implements Engine {
	/**
	 * @var array The options passed to the engine
	 */
	private $options;

	/**
	 * @inheritDoc
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeEditorName(): ?string {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getGeSHiName(): ?string {
		return null;
	}

	/**
	 * Returns the options passed to the engine.
	 *
	 * @return array
	 */
	protected function getOptions(): array {
		return $this->options;
	}
}
