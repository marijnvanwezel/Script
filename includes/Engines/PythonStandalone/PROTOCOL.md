# Python Standalone protocol definition

The Python standalone engine uses a message-based protocol for communication between PHP and the Python engine. Messages passed between PHP and Python are JSON-encoded.

## Messages send from PHP to Python

### `invoke`

Loads and executes some Python code and returns the result.

Message parameters:
* opcode: `invoke`
* source: The Python code to load
* mainName: The name of the function in `source` to execute
* args: The arguments to pass to `mainName`

On success, the response message is:

* status: `success`
* result: The return value of the function

On failure, the response message is:

* status: `error`
* 