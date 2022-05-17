from json import JSONDecodeError, loads, dumps
from RestrictedPython import compile_restricted_exec, safe_builtins

# The environment in which the given code will be executed
EXECUTION_ENVIRONMENT = {"__builtins__": safe_builtins}

class PybuntoException(Exception):
    """Exception raised for any error that should be caught and
    handled by the Pybunto engine.

    Attributes:
        code -- unique exception code
        message -- short human-readable message detailing the error
    """

    def __init__(self, code, message):
        super().__init__(message)
        
        self.code = code
        self.message = message

    def toDict(self):
        return {
            "status": "error",
            "code": self.code,
            "message": self.message
        }

class InvalidInputError(PybuntoException):
    """Raised when invalid input is supplied to the engine."""

    INPUT_INVALID_JSON = 10
    INPUT_INVALID_SOURCE = 11
    INPUT_INVALID_NAME = 12
    INPUT_INVALID_ARGS = 13
    INPUT_INVALID_FRAME = 14

class ModuleCompilationError(PybuntoException):
    """Raised when the compilation of a module failed.
    
    Attributes:
        errors -- list of errors raised by the engine
    """

    COMPILATION_ERROR = 20

    def __init__(self, errors):
        super().__init__(self.COMPILATION_ERROR, "Compilation error")

        self.errors = errors

    def toDict(self):
        dict = super().toDict()
        dict["errors"] = self.errors

        return dict

class FunctionInvocationError(PybuntoException):
    """Raised when the invocation of a function resulted in an error.
    
    Attributes:
        error -- the error that was raised
    """

    INVOCATION_ERROR = 30

    def __init__(self, error):
        super().__init__(self.INVOCATION_ERROR, error.__class__.__name__ + ": " + str(error))

def main():
    """Main engine entrypoint"""

    try:
        (module_source, function_name, function_args, frame) = readInput()

        function_result = invokeFunction(
            code=compileModule(module_source), 
            name=function_name, 
            args=function_args,
            frame=frame
        )

        dispatch({"status": "success", "result": function_result})
    except PybuntoException as error:
        dispatch(error.toDict())
        return

def readInput():
    """Reads the input JSON and parses it. May throw an InvalidInputError when the
    input given is not of a valid form.
    """

    try:
        parsed_input = loads(input())
    except JSONDecodeError:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_JSON, 
            "Invalid JSON"
        )

    if "source" not in parsed_input:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_SOURCE, 
            "Missing input source"
        )

    if type(parsed_input["source"]) is not str:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_SOURCE, 
            "Invalid input source (must be a string)"
        )

    if "name" not in parsed_input:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_NAME, 
            "Missing input name"
        )

    if type(parsed_input["name"]) is not str:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_NAME, 
            "Invalid input name (must be a string)"
        )

    if "frame" not in parsed_input:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_FRAME, 
            "Missing input frame"
        )

    if type(parsed_input["frame"]) is not dict:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_FRAME, 
            "Invalid input frame (must be a dictionary)"
        )

    # Args may optionally be omitted
    args = parsed_input["args"] if "args" in parsed_input else {}

    if type(args) is not dict:
        raise InvalidInputError(
            InvalidInputError.INPUT_INVALID_ARGS,
            "Invalid input args (must be a dictionary)"
        )

    return (
        parsed_input["source"], 
        parsed_input["name"], 
        args,
        parsed_input["frame"]
    )

def compileModule(module_source):
    (module_code, errors, _, _) = compile_restricted_exec(
        source=module_source,
        filename='<module code>',
        dont_inherit=True
    )

    if not module_code:
        raise ModuleCompilationError(errors)

    return module_code

def execModule(code):
    """Executes the given code and returns the resulting local environment. 
    This environment contains all top level definitions (such as functions) and 
    can be used to interact with the executed module.
    """

    local_environment = {}
    exec(code, EXECUTION_ENVIRONMENT, local_environment)

    return local_environment

def invokeFunction(code, name, args, frame):
    try:
        return execModule(code)[name](frame, **args)
    except BaseException as error:
        raise FunctionInvocationError(error)

def dispatch(info):
    print(dumps(info))

if __name__ == '__main__':
    main()
    