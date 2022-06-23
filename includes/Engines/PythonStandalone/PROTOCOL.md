# Python Standalone protocol definition

The Python standalone engine uses a message-based protocol for communication
between PHP and the Python engine. Messages passed between PHP and Python are
JSON-encoded.

A *request message* is a message sent from one application to another requesting
some action to be performed. A request message always has an `opcode` attribute,
indicating the action to perform, and a `result` attribute. The format of this
`result` attribute depends on the `opcode`.

Every request message demands exactly one *response message*. This response
message always has a `status` attribute, indicating whether the request
succeeded (`true`) or failed (`false`). Depending on the corresponding `opcode`,
it can have additional message attributes. When a request message is sent, the
responder does not need to send the corresponding response message immediately
as its next message. It can instead send its own request message, creating a
stack of pending requests. This allows re-entrant and recursive calls.

## Request messages send from PHP to Python

### `setcpulimit`

Sets a limit on the amount of time the engine may take to execute using
`resource.setrlimit`.

Message parameters:
* opcode: `setcpulimit`
* limit: The time limit in seconds

On success, the response message is:

* status: `success`
* result: `{}`

### `setmemlimit`

Sets a limit on the amount of memory the engine may occupy using
`resource.setrlimit`.

Message parameters:
* opcode: `setmemlimit`
* limit: The memory limit in bytes

On success, the response message is:

* status: `success`
* result: `{}`

### `invoke`

Loads and executes some Python code and returns the result.

Message parameters:
* opcode: `invoke`
* source: The Python code to load
* mainName: The name of the function in `source` to execute
* args: The arguments to pass to `mainName`

On success, the response message is:

* status: `success`
* result:
  * value: The return value of the function

### `validate`

Loads some Python code and reports any (syntax) errors in the specified source.

Message parameters:
* opcode: `validate`
* source: The Python code to validate

On success, the response message is:

* status: `success`
* result:
    * valid: `true` if the source was valid, `false` otherwise
    * errors: The list of errors in the source (empty if valid)

### Request messages sent from Python to PHP



## Errors

A response message can have a status of either `success` or `error`. When the
status of a response message is set to `error`, the following attributes will
also be set:

* code: The code of the error message (unique for each different type of error)
* message: A human-readable message detailing the error

Depending on the error type, additional attributes may be available.

### Input errors

Raised when invalid input is given to the engine.

#### `InvalidJsonError`

Raised when the given input is not valid JSON.

Attributes:
* code: `10`
* message: `Invalid JSON`

#### `MissingOpcodeError`

Raised when no `opcode` is given.

Attributes:
* code: `11`
* message: `Missing opcode`

### `InvalidOpcodeError`

Raised when an invalid `opcode` is given.

Attributes:
* code: `12`
* message: `Invalid opcode`

### `MissingAttributeError`

Raised when a required attribute is missing.

Attributes:
* code: `13`
* message: `Missing attribute`
* attribute_name: The name of the attribute that is missing

### `InvalidAttributeError`

Raised when an attribute has an invalid value.

Attributes:
* code: `14`
* message: Short human-readable message detailing why the attribute is invalid
* attribute_name: The name of the attribute that is invalid

## Library errors

Raised when an error occurred while loading a library.

### `LibraryReadError`

Raised when a library file could not be read.

Attributes:
* code: `20`
* message: Short human-readable message detailing why the file could not be read
* library_path: The path to the file that could not be read

### `LibraryCompilationError`

Raised when the compilation of a library failed.

Attributes:
* code: `21`
* message: `Library compilation error`
* library_path: The path to the file that could not be compiled
* errors: List of errors raised by the compiler

## Interpreter errors

Raised when an error occurs in the interpreter.

### `CompilationError`

Raised when the compilation of some source code failed.

Attributes:
* code: `30`
* message: `Compilation error`
* errors: List of errors raised by the compiler
