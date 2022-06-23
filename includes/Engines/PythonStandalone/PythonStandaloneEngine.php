<?php

namespace MediaWiki\Extension\Script\Engines\PythonStandalone;

use MediaWiki\Extension\Script\Engines\BaseEngine;
use MediaWiki\Extension\Script\Engines\PythonStandalone\Exceptions\BrokenPipeException;
use MediaWiki\Extension\Script\Engines\PythonStandalone\Exceptions\UnexpectedMessageException;
use MediaWiki\Extension\Script\Exceptions\ScriptException;
use MediaWiki\Extension\Script\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\Script\ScriptServices;
use MediaWiki\Shell\Shell;
use PPFrame;
use Status;

/**
 * Engine implementation using Python.
 */
class PythonStandaloneEngine extends BaseEngine {
	private const ENGINE_PATH = __DIR__ . '/pylib/engine.py';
	private const DEFAULT_CPU_LIMIT = 7; // 7 seconds
	private const DEFAULT_MEM_LIMIT = 52428800; // 50 MiB

	private const LIBRARIES_REGISTERED_FLAG = 0b00000001;

	/**
	 * @var EngineProcess|null The external engine process
	 */
	private $engineProcess = null;

	/**
	 * @throws InvalidEngineSpecificationException
	 */
	public function __construct( array $options ) {
		if ( !isset( $options['pythonExecutable'] ) ) {
			throw new InvalidEngineSpecificationException(
				'py',
				'script-invalid-engine-specification-reason-missing-attribute',
				['pythonExecutable']
			);
		}

		parent::__construct( $options );
	}

	/**
	 * Makes sure the process is closed whenever an instance of this class is destructed.
	 */
	public function __destruct() {
		if ( !isset( $this->engineProcess ) ) {
			// No process is open, we don't need to close it
			return;
		}

		try {
			// Dispatch the exit opcode and let the engine shut itself down
			$this->engineProcess->write( ['opcode' => 'exit'] );
			$this->engineProcess->close();
		} catch ( BrokenPipeException $exception ) {
			ScriptServices::getLogger()->warning( "Broken pipe during shutdown" );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function executeScript( string $script, string $mainName, PPFrame $frame ): string {
		// Register any libraries upon (first) execution of a script
		$this->registerLibraries();

		// TODO
	}

	/**
	 * @inheritDoc
	 */
	public function validateSource( string $source, Status &$status ): void {
		$result = $this->validate( $source );

		if ( !isset( $result['status'] ) ) {
			throw new UnexpectedMessageException();
		}

		if ( $result['status'] === 'error' ) {
			// The validation could not be completed. This does NOT mean the script is invalid, it means the script
			// could not be validated due to other errors.
			throw new ScriptException( 'script-could-not-validate' );
		}

		if ( !isset( $result['result']['valid'] ) ) {
			throw new UnexpectedMessageException();
		}

		if ( $result['result']['valid'] === false ) {
			foreach ( $result['result']['errors'] as $error ) {
				$status->fatal( 'script-python-syntax-error', $error );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getVersion(): ?string {
		$handle = popen( Shell::escape( $this->getOptions()['pythonExecutable'] ) . ' -V', 'r' );

		if ( $handle ) {
			$version = fgets( $handle );
			pclose( $handle );

			if ( $version && preg_match( '/^(Python) (\S+)/', $version, $m ) ) {
				return $m[2];
			}
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getHumanName(): string {
		return wfMessage( 'script-python-human-name' )->parse();
	}

	/**
	 * @inheritDoc
	 */
	public function getLogo(): string {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAAABGdBTUEAALGPC/xhBQAAACBjSF' .
			'JNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACClBMVEUAAABAgL83erM4ebA2eK43d603dqs3dak2dKg3c' .
			'6Q5erM4ebA3d643caI3ebA3ea0AgIA3dqs3cKA3d643d603d6s3cJ44dq42dqs2dKk2dKc2cqU2cqM2bpw2ebQ3ebA3eK43d603' .
			'dqs3dqk3dKg3c6U3cqQ3caI2bZr/2Ev/2Ej/1kf/1EU3erE4ebA2bJn/2Uj/0kI3eLA2a5f/1kb/0T83eK42a5T/1UT/zz83caI' .
			'4b6A4b542bpw2bps2bJk2a5c1apX80kb/00Q3dqs3cqM3cKH/31H/3k//3E7/3Ez/2Ur/10r/1kf/1UU4dak3caL/4FH/yzo3c6' .
			'c4b6D/3k7/yTk1c6U3cJ7/3E7/yjn/xzg3caM3b543bp3/20z/00T/0kL/0UH/0D//zj//zTz/yzr/yjn/xzj/2kv/00P/0EL/0' .
			'ED/zj7/zDz/2En/zj7/zTz/yzv/10f/zj7/zDP/yjz/yjr/1Ub/1ET/zDv/yTf/1UL/0UH/0ED/zj7/zTz/zDz/yzn/yjj/v0A3' .
			'd603dqs3dak3dKc3c6U3cqQ3caI3cKA3b542bpw3eK42bZr/1kf/1UX/00Q2bJn/0kI3cqM2a5f/0UD/0UH/zz//zj3/1ET/zDz' .
			'/3k//3E7/20z/2Ur/2En/1Ub/zTz/10f/zj7/yzr/2Uv/0kP/0D//00P/0ED///9ywrbbAAAAhXRSTlMABGu/6vn25bJUkOWrfs' .
			'+DAu/bz/bV4G6IiIiI+OA9uMzMzMzMzMz84HDu5ncu+eB4VaHRe73fda/00K6qqqqqoldQ/PX+VFiou7u7u7vU4b9r8KiOwLA+i' .
			'836N2Hugs3NzMzMzMzMskDNioiIiHjN0vDiyPsKb+Jm/rmdQqXY7vHjuWkEM28rJQAAAAFiS0dErSBiwh0AAAAHdElNRQfhCAoJ' .
			'ATvpwCrIAAABH0lEQVQY02NgAAFGJmYWVjZ2Dk4GJMDFzdPa1t7R2cWLJMjHLyAIEusWQhIUFhEFi/WIIQmKS0hKSct09/TKQgX' .
			'k5BUUlZRVVNXUe3r7NDS1tHUYGHT1+iF2ANX1TdA3mDhpsiGDEbLYFGOTSZOnmjKYgcWmQcSmm1tMnjrDkkEELGZlbWNrZ+/g6O' .
			'Q8deas2QwuQDFXN3cPTy9vH1+/OSCxuQz+QHUBgfPmL1i4aOJisNiSIIZgoHkhoSCxpWCxZUuWhzGEu3b3REQii0VFMzDECMXGx' .
			'a8AiiUkJiWnpKalZ0B9lQlSl5Wdk5uH5Pd8oNjKmasKCouQBIsXz1k9c1VJaVk5kmBF5eqZa5YtqSqvRg76mtq6+obGpuYWMA8A' .
			'CBl/Ca2P4ogAAAAldEVYdGRhdGU6Y3JlYXRlADIwMTctMDgtMTBUMDk6MDE6NTkrMDA6MDAkiJ0oAAAAJXRFWHRkYXRlOm1vZGl' .
			'meQAyMDE3LTA4LTEwVDA5OjAxOjU5KzAwOjAwVdUllAAAAABJRU5ErkJggg==';
	}

	/**
	 * @inheritDoc
	 */
	public function getGeSHiName(): string {
		return 'python';
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeEditorName(): string {
		return 'python';
	}

	/**
	 * Dispatches the "setcpulimit" opcode.
	 *
	 * @param int $limitInSeconds The max execution time in seconds
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	protected function setCPULimit( int $limitInSeconds ): array {
		return $this->dispatch( [
			'opcode' => 'setcpulimit',
			'limit' => $limitInSeconds
		] );
	}

	/**
	 * Dispatches the "setmemlimit" opcode.
	 *
	 * @param int $limit The max amount of memory to use in bytes
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	protected function setMemLimit( int $limit ): array {
		return $this->dispatch( [
			'opcode' => 'setmemlimit',
			'limit' => $limit
		] );
	}

	/**
	 * Dispatches the "validate" opcode.
	 *
	 * @param string $source The source to validate
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	protected function validate( string $source ): array {
		return $this->dispatch( [
			'opcode' => 'validate',
			'source' => $source
		] );
	}

	/**
	 * Dispatches the "invoke" opcode.
	 *
	 * @param string $source The source of the script to run
	 * @param string $mainName The name of the function to call
	 * @param array $args The arguments to pass to the function
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	protected function invoke( string $source, string $mainName, array $args ): array {
		return $this->dispatch( [
			'opcode' => 'invoke',
			'source' => $source,
			'main' => $mainName,
			'args' => $args
		] );
	}

	/**
	 * Dispatches the given message to the engine process.
	 *
	 * @param array $message The message to dispatch
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	private function dispatch( array $message ): array {
		$engineProcess = $this->getEngineProcess();

		try {
			return $engineProcess->dispatch( $message );
		} catch ( BrokenPipeException $exception ) {
			unset( $this->engineProcess );
			$this->librariesRegistered = false;

			throw $exception;
		}
	}

	/**
	 * Returns the engine process.
	 *
	 * @return EngineProcess
	 * @throws BrokenPipeException
	 */
	private function getEngineProcess(): EngineProcess {
		if ( !isset( $this->engineProcess ) ) {
			// Invoke the engine process lazily whenever this function is called instead of invoking it in the
			// constructor. This is done to make sure we do not unnecessarily open a process to the Python engine
			// each time this class is constructed.
			$this->engineProcess = $this->newEngine();
		}

		return $this->engineProcess;
	}

	/**
	 * Creates a new EngineProcess and returns that
	 *
	 * @return EngineProcess
	 * @throws BrokenPipeException
	 */
	private function newEngine(): EngineProcess {
		$options = $this->getOptions();

		$process = new EngineProcess( $options['pythonExecutable'], self::ENGINE_PATH );
		$process->dispatch( ['opcode' => 'setcpulimit', 'limit' => $options['cpuLimit'] ?? self::DEFAULT_CPU_LIMIT] );
		$process->dispatch( ['opcode' => 'setmemlimit', 'limit' => $options['memoryLimit'] ?? self::DEFAULT_MEM_LIMIT] );

		return $process;
	}

	/**
	 * Registers (external) libraries if necessary. Libraries are ever only registered once per process instance. This
	 * function therefore does nothing if libraries have already been registered with this process.
	 *
	 * @return void
	 * @throws BrokenPipeException
	 */
	private function registerLibraries() {
		$engineProcess = $this->getEngineProcess();

		if ( $engineProcess->hasFlag( self::LIBRARIES_REGISTERED_FLAG ) ) {
			return;
		}

		$libraries = $this->getStandardLibraries();
		ScriptServices::getHookRunner()->onScriptRegisterExternalPythonLibraries( $libraries );

		$engineProcess->setFlag( self::LIBRARIES_REGISTERED_FLAG );
	}
}
