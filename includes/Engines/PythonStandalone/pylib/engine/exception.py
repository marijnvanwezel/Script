class EngineError(Exception):
    """Exception raised for any error that should be caught and
    handled by the engine.

    Attributes:
        code -- unique exception code
        message -- short human-readable message detailing the error
    """

    def __init__(self, code, message):
        super().__init__(message)
        
        self.code = code
        self.message = message

    def to_dict(self):
        return {
            "status": "error",
            "code": self.code,
            "message": self.message
        }


class InputError(EngineError):
    """Raised when invalid input is supplied to the engine."""

    INPUT_INVALID_JSON_ERROR = 10
    INPUT_MISSING_OPCODE_ERROR = 11
    INPUT_INVALID_OPCODE_ERROR = 12
    INPUT_MISSING_ATTRIBUTE_ERROR = 13
    INPUT_INVALID_ATTRIBUTE_ERROR = 14


class InvalidJSONError(InputError):
    """Raised when the given input is not valid JSON."""

    def __init__(self):
        super().__init__(self.INPUT_INVALID_JSON_ERROR, "Invalid JSON")


class MissingOpcodeError(InputError):
    """Raised when no opcode is given."""

    def __init__(self):
        super().__init__(self.INPUT_MISSING_OPCODE_ERROR, "Missing opcode")


class InvalidOpcodeError(InputError):
    """Raised when an invalid opcode is given."""

    def __init__(self):
        super().__init__(self.INPUT_INVALID_OPCODE_ERROR, "Invalid opcode")


class MissingAttributeError(InputError):
    """Raised when a required attribute is missing.
    
    Attributes:
        attribute -- the name of the attribute that is missing
    """

    def __init__(self, attribute):
        super().__init__(self.INPUT_MISSING_ATTRIBUTE_ERROR, "Missing attribute")

        self.attribute = attribute

    def to_dict(self):
        dict = super().to_dict()
        dict["attribute_name"] = self.attribute

        return dict


class InvalidAttributeError(InputError):
    """Raised when an attribute has an invalid value.
    
    Attributes:
        attribute -- the name of the attribute that is invalid
        message -- short human-readable message detailing why the attribute is invalid
    """

    def __init__(self, attribute, message):
        super().__init__(self.INPUT_INVALID_ATTRIBUTE_ERROR, "Invalid attribute: " + message)

        self.attribute = attribute

    def to_dict(self):
        dict = super().to_dict()
        dict["attribute_name"] = self.attribute

        return dict


class LibraryError(EngineError):
    """Raised when an error occurred while loading a library.
    
    Attributes:
        library_path -- the path to the library
    """

    LIBRARY_READ_ERROR = 20
    LIBRARY_COMPILATION_ERROR = 21

    def __init__(self, code, message, library_path):
        super().__init__(code, message)

        self.library_path = library_path

    def to_dict(self):
        dict = super().to_dict()
        dict["library_path"] = self.library_path
        
        return dict


class LibraryReadError(LibraryError):
    """Raised when a library file could not be read.
    
    Attributes:
        message -- short human-readable message detailing why the file could not be read
        library_path -- the path to the file that could not be read
    """

    def __init__(self, message, library_path):
        super().__init__(self.LIBRARY_READ_ERROR, message, library_path)


class LibraryCompilationError(LibraryError):
    """Raised when the compilation of a library failed.
    
    Attributes:
        library_path -- the path to the file that could not be compiled
        errors -- list of errors raised by the compiler
    """

    def __init__(self, library_path, errors):
        super().__init__(self.LIBRARY_COMPILATION_ERROR, "Library compilation error", library_path)

        self.errors = errors

    def to_dict(self):
        dict = super().to_dict()
        dict["errors"] = self.errors

        return dict


class InterpreterError(EngineError):
    """Raised when an error occurs in the interpreter."""

    COMPILATION_ERROR = 30


class CompilationError(InterpreterError):
    """Raised when the compilation of some source code failed.
    
    Attributes:
        errors -- list of errors raised by the compiler
    """

    def __init__(self, errors):
        super().__init__(self.COMPILATION_ERROR, "Compilation error")

        self.errors = errors

    def to_dict(self):
        dict = super().to_dict()
        dict["errors"] = self.errors

        return dict