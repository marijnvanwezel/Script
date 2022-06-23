<?php

namespace MediaWiki\Extension\Script\Engines\PythonStandalone;

use MediaWiki\Extension\Script\Engines\PythonStandalone\Exceptions\BrokenPipeException;
use MediaWiki\Extension\Script\ScriptServices;
use MediaWiki\Shell\Shell;
use RuntimeException;

/**
 * Thin wrapper around a process resource and its I/O pipes.
 *
 * Handles low-level communication between engine.py and PHP using inter-process communication (IPC).
 */
class EngineProcess {
	/**
	 * @var resource The engine.py process
	 */
	private $process;

	/**
	 * @var resource The STDIN pipe of engine.py, used for writing to engine.py
	 */
	private $pipeIn;

	/**
	 * @var resource The STDOUT pipe of engine.py, used for reading from engine.py
	 */
	private $pipeOut;

	/**
	 * @var resource The STDERR pipe of engine.py, used for reading from engine.py
	 */
	private $pipeErr;

	/**
	 * @var int The set flags
	 */
	private $flags = 0b0;

	/**
	 * @var bool Whether the process is closed
	 */
	private $isClosed = false;

	/**
	 * @param string $executable The Python executable used to invoke Python (e.g. "python3")
	 * @param string $enginePath The path to engine.py
	 */
	public function __construct( string $executable, string $enginePath ) {
		$command = Shell::escape( $executable ) . ' ' . Shell::escape( $enginePath );
		$descriptors = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w']
		];

		// Clear the last error, so we can be sure that any error in "error_get_last()" was emitted from
		// "proc_open()".
		error_clear_last();

		$this->process = proc_open( $command, $descriptors, $pipes );

		if ( !is_resource( $this->process ) ) {
			$errorMessage = error_get_last()['message'] ?? 'Unknown error';

			ScriptServices::getLogger()->error( 'Failed to open process "{path}" using executable "{executable}": {message}', [
				'path' => $enginePath,
				'executable' => $executable,
				'message' => $errorMessage
			] );

			// Throw an unchecked exception
			throw new RuntimeException(
				wfMessage( 'script-python-open-process-error', $enginePath, $executable, $errorMessage )->parse()
			);
		}

		$this->pipeIn = $pipes[0];
		$this->pipeOut = $pipes[1];
		$this->pipeErr = $pipes[2];
	}

	/**
	 * Clean up the process if it is still open.
	 */
	public function __destruct() {
		// Send a SIGKILL to the process if necessary. The caller is responsible for gracefully closing the process
		// through the "close()" method before this class is destructed. This code is only here to make sure we don't
		// end up with a bunch of orphaned processes.
		if ( $this->isClosed ) {
			return;
		}

		proc_terminate( $this->process, 9 );
		ScriptServices::getLogger()->warning( 'Forcefully terminated Python engine process using SIGKILL' );
	}

	/**
	 * Writes the given JSON message to the engine, waits for the result, and returns that.
	 *
	 * @param array $message The message to send
	 * @return array The response
	 * @throws BrokenPipeException When the communication failed
	 */
	public function dispatch( array $message ): array {
		$this->write( $message );
		return $this->read();
	}

	/**
	 * Reads a JSON message from the engine.
	 *
	 * @return array
	 * @throws BrokenPipeException When the communication failed
	 */
	public function read(): array {
		$input = fgets( $this->pipeOut );

		if ( $input === false ) {
			// Often when we encounter a broken pipe, this is because the Python process quit unexpectedly, so we
			// check STDERR.
			throw new BrokenPipeException( fread( $this->pipeErr, 8192 ) ?: null );
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
	public function write( array $message ): void {
		$result = fputs( $this->pipeIn, json_encode( $message ) . "\n" );

		if ( $result === false ) {
			// Often when we encounter a broken pipe, this is because the Python process quit unexpectedly, so we
			// check STDERR.
			throw new BrokenPipeException( fread( $this->pipeErr, 8192 ) ?: null );
		}
	}

	/**
	 * Closes the process gracefully.
	 *
	 * @return void
	 */
	public function close(): void {
		if ( $this->isClosed ) {
			return;
		}

		fclose( $this->pipeIn );
		fclose( $this->pipeOut );
		fclose( $this->pipeErr );

		proc_close( $this->process );

		$this->isClosed = true;
	}

	/**
	 * Sets the given binary flag. This can be used to keep track of attributes that are bound to a specific engine
	 * process instance (such as whether libraries have been registered).
	 *
	 * @note A flag can can only be "unset" by creating a new instance of this class.
	 *
	 * @param int $flag A binary flag
	 * @return void
	 */
	public function setFlag( int $flag ): void {
		$this->flags |= $flag;
	}

	/**
	 * Returns true if and only if the given flag has been set.
	 *
	 * @param int $flag A binary flag
	 * @return bool True if the flag is set, false otherwise
	 */
	public function hasFlag( int $flag ): bool {
		return $this->flags & $flag;
	}
}
