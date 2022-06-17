<?php

namespace MediaWiki\Extension\FFI\Engines\PythonStandalone;

use MediaWiki\Extension\FFI\Exceptions\BrokenPipeException;
use MediaWiki\Extension\FFI\Exceptions\FFIException;
use MediaWiki\Extension\FFI\Exceptions\InvalidEngineSpecificationException;
use MediaWiki\Extension\FFI\FFIServices;
use MediaWiki\Shell\Shell;

/**
 * Handles low-level communication between "engine.py" and PHP using inter-process communication (IPC).
 */
class PythonStandaloneInterpreter {
	private const ENGINE_PATH = __DIR__ . '/pylib/engine.py';

	/**
	 * @var resource The engine.py process
	 */
	private $process;

	/**
	 * @var resource The STDIN pipe of engine.py, used for writing to engine.py
	 */
	private $pipe_in;

	/**
	 * @var resource The STDOUT pipe of engine.py, used for reading from engine.py
	 */
	private $pipe_out;

	/**
	 * @var resource The STDERR pipe of engine.py, used for reading from engine.py
	 */
	private $pipe_err;

	/**
	 * @param string $executable
	 * @throws FFIException
	 */
	public function __construct( string $executable ) {
		if ( !function_exists( 'proc_open' ) ) {
			// TODO: i18n message
			throw new FFIException( 'ffi-python-missing-proc-open-error' );
		}

		$command = Shell::escape( $executable ) . ' ' . Shell::escape( self::ENGINE_PATH );
		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'r']
		];

		// Clear the last error, so we can be sure that any error in "error_get_last()" was emitted from
		// "proc_open()".
		error_clear_last();

		$this->process = proc_open( $command, $descriptors, $pipes );

		if ( !is_resource( $this->process ) ) {
			$error = error_get_last()['message'] ?? 'Unknown error';

			FFIServices::getLogger()->error(
				'Failed to open process "{path}" using executable "{executable}": {message}',
				[
					'path' => self::ENGINE_PATH,
					'executable' => $executable,
					'message' => $error['message']
				]
			);

			throw new FFIException(
				'ffi-python-open-process-error',
				[self::ENGINE_PATH, $executable, $error]
			);
		}

		$this->pipe_in = $pipes[0];
		$this->pipe_out = $pipes[1];
		$this->pipe_err = $pipes[2];
	}

	/**
	 * Clean up the process if it is still open.
	 */
	public function __destruct() {
		// Send a SIGKILL to the process if necessary. The caller is responsible for gracefully closing the process
		// through the "close()" method before this class is destructed. This code is only here to make sure we don't
		// end up with a bunch of orphaned processes.
		$this->terminate();
	}

	/**
	 * Returns the Python version of the given Python executable.
	 *
	 * @param string $executable
	 * @return string|null
	 */
	public static function getVersion( string $executable ): ?string {
		$handle = popen( Shell::escape( $executable ) . ' -V', 'r' );

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
	 * Sends the "exit" opcode and closes the process gracefully.
	 *
	 * @return void
	 */
	public function exit(): void {
		if ( !isset( $this->process ) ) {
			return;
		}

		try {
			$this->write( [
				'opcode' => 'exit'
			] );
		} catch ( BrokenPipeException $exception ) {
			FFIServices::getLogger()->warning( "Broken pipe during shutdown, terminating using SIGKILL" );

			// Make sure the process is killed, so we do not end up in a deadlock or with a bunch of
			// orphaned processes.
			$this->terminate();

			return;
		}

		fclose( $this->pipe_in );
		fclose( $this->pipe_out );
		fclose( $this->pipe_err );

		proc_close( $this->process );

		unset( $this->pipe_in, $this->pipe_out, $this->pipe_err, $this->process );
	}

	/**
	 * Dispatches the "validate" opcode.
	 *
	 * @param string $source The source to validate
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	public function validate( string $source ): array {
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
	public function invoke( string $source, string $mainName, array $args ): array {
		return $this->dispatch( [
			'opcode' => 'invoke',
			'source' => $source,
			'main' => $mainName,
			'args' => $args
		] );
	}

	/**
	 * @param int $limit
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	public function setCPULimit( int $limit ): array {
		return $this->dispatch( [
			'opcode' => 'setcpulimit',
			'limit' => $limit
		] );
	}

	/**
	 * @param int $limit
	 * @return array The response
	 * @throws BrokenPipeException
	 */
	public function setMemLimit( int $limit ): array {
		return $this->dispatch( [
			'opcode' => 'setmemlimit',
			'limit' => $limit
		] );
	}

	/**
	 * Writes the given JSON message to the engine, waits for the result, and returns that.
	 *
	 * @param array $message The message to send
	 * @return array The response
	 * @throws BrokenPipeException When the communication failed
	 */
	private function dispatch( array $message ): array {
		$this->write( $message );
		return $this->read();
	}

	/**
	 * Reads a JSON message from the engine.
	 *
	 * @return array
	 * @throws BrokenPipeException When the communication failed
	 */
	private function read(): array {
		$input = fgets( $this->pipe_out );

		if ( $input === false ) {
			FFIServices::getLogger()->error( 'Failed to read data from Python engine' );
			throw new BrokenPipeException();
		}

		return json_decode( $input, true );
	}

	/**
	 * Writes a JSON message to engine.
	 *
	 * @param array $message
	 * @return void
	 * @throws BrokenPipeException When the communication failed
	 */
	private function write( array $message ): void {
		$result = fputs( $this->pipe_in, json_encode( $message ) . "\n" );

		if ( $result === false ) {
			FFIServices::getLogger()->error( 'Failed to write data to Python engine' );
			throw new BrokenPipeException();
		}
	}

	/**
	 * Closes the process forcefully.
	 *
	 * @return void
	 * @see PythonStandaloneInterpreter::__destruct()
	 */
	private function terminate(): void {
		if ( !isset( $this->process ) ) {
			return;
		}

		// Send a SIGKILL
		proc_terminate( $this->process, 9 );
		FFIServices::getLogger()->warning( 'Forcefully terminated Python engine process using SIGKILL' );
		proc_close( $this->process );

		unset( $this->pipe_in, $this->pipe_out, $this->pipe_err, $this->process );
	}
}