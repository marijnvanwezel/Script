import resource
import signal
import sys
from engine.exception import *
from json import loads, dumps, JSONDecodeError
from sys import exit

import RestrictedPython as compiler

class Engine:
    def __init__(self):
        self.environment = compiler.safe_globals
        

    def run(self) -> None:
        """Runs the engine. This function will never return."""
        OPCODES = {
            "exit": Engine.__handle_exit,
            "loadlibrary": Engine.__handle_load_library,
            "validate": Engine.__handle_validate,
            "setcpulimit": Engine.__handle_set_cpu_limit,
            "setmemlimit": Engine.__handle_set_mem_limit
        }

        while True:
            try:
                message = self.__read()
                
                if "opcode" not in message:
                    raise MissingOpcodeError()

                if message["opcode"] not in OPCODES:
                    raise InvalidOpcodeError()

                # If the opcode handler returns a result, we see that as a success. Any handler
                # should throw an exception if something goes wrong, and NOT return an error
                # message.
                self.__write_success(OPCODES[message["opcode"]](self, message))
            except EngineError as engine_error:
                self.__write(engine_error.to_dict())



    def __handle_exit(self, message: dict) -> dict:
        """Handle the 'exit' opcode"""
        exit(0)


    def __handle_load_library(self, message: dict) -> dict:
        """Handle the 'loadlibrary' opcode"""
        if "library_path" not in message:
            raise MissingAttributeError("library_path")

        # "library_name" is the name of the library used in error reporting
        library_name = message["library_name"] if "library_name" in message else "<library code>"

        # "library_path" is the path to the Python file implementing the library
        library_path = message["library_path"]

        try:
            with open(library_path) as file:
                library_source = file.read()
        except OSError as error:
            raise LibraryReadError(error.strerror, library_path)

        # "interface_functs" is a dictionary of function identifiers, where the key is the
        # human name of the function, and the value a unique identifier for the associated
        # function in PHP.
        interface_functs = message["interface_functs"] if "interface_functs" in message else []

        # TODO: Create environment with appropriate PHP interface

        try:
            self.__load_library(library_source, library_name)
        except CompilationError as error:
            raise LibraryCompilationError(library_path, error.errors)


    def __handle_validate(self, message: dict) -> dict:
        """Handle the 'validate' opcode"""
        if "source" not in message:
            raise MissingAttributeError("source")

        try:
            self.__compile(source=message["source"])
        except CompilationError as error:
            return {'valid': False, 'errors': error.errors}

        return {'valid': True, 'errors': {}}


    def __handle_set_cpu_limit(self, message: dict) -> dict:
        """Handle the 'setcpulimit' opcode"""
        if "limit" not in message:
            raise MissingAttributeError("limit")

        limit = message["limit"]

        if not isinstance(limit, int):
            raise InvalidAttributeError("limit", "'limit' must be of type 'int'")

        _, hard = resource.getrlimit(resource.RLIMIT_CPU)

        # Set the "soft" limit of the resource, so we can still implement some appropriate
        # handling.
        resource.setrlimit(resource.RLIMIT_CPU, (limit, hard))

        # Whenever the soft limit set above is exceeded, a SIGXCPU is sent to the engine.
        # We set a function to handle this interrupt.
        signal.signal(signal.SIGXCPU, self.__handle_sigxcpu)

        return {}


    def __handle_set_mem_limit(self, message: dict) -> dict:
        """Handle the 'setmemlimit' opcode"""
        if "limit" not in message:
            raise MissingAttributeError("limit")

        limit = message["limit"]

        if not isinstance(limit, int):
            raise InvalidAttributeError("limit", "'limit' must be of type 'int'")

        _, hard = resource.getrlimit(resource.RLIMIT_AS)
        resource.setrlimit(resource.RLIMIT_AS, (limit, hard))


    def __load_library(self, source: str, name: str) -> None:
        """Loads the given library source into the interpreter's environment.
        
        Parameters:
            source : str -- the source of the library to load
            name : str -- the name of the library to load for use in error reporting
        """

        code = self.__compile(source, name)

        # TODO: IS SAFE_GLOBALS ALTERED?
        library_env = self.__execute(code, compiler.safe_globals)

        self.environment = {**self.environment, **library_env}


    def __compile(self, source: str, name: str = '<script>'):
        """Compiles the given source code into a Python 'code' object.
        
        Parameters:
            source : str -- the source to compile
            name : str, optional -- the name of the file to use in error reporting

        Returns: A code object.
        """

        (code, errors, _, _) = compiler.compile_restricted_exec(source=source, filename=name, dont_inherit=True)

        if not code:
            raise CompilationError(errors)

        return code

    
    def __execute(self, code: object) -> dict:
        """Executes the given code and returns the resulting local environment. 
        This environment contains all top level definitions (such as functions) and 
        can be used to interact with the executed script.

        Parameters:
            code : object -- a code object returned from "compile"

        Returns: The resulting local environment.
        """
        local_env = {}

        # Pass a copy to make sure that the code we are executing is not able to alter 
        # the environment.
        exec(code, self.environment.copy(), local_env)

        return local_env


    def __execute_safe_globals(self, code: object) -> dict:
        """Executes the given code in the "safe_globals" environment and returns
        the resulting local environment. This environment contains all top level 
        definitions (such as functions) and can be used to interact with the 
        executed script.

        Parameters:
            code : object -- a code object returned from "compile"

        Returns: The resulting local environment.
        
        See: self.__execute for a function to execute code in the loaded environment.
        """
        local_env = {}

        # Pass a copy to make sure that the code we are executing is not able to alter 
        # the environment.
        exec(code, compiler.safe_globals.copy(), local_env)

        return local_env


    def __handle_sigxcpu(self) -> None:
        """Handles the SIGXCPU interrupt"""
        self.__write({
            'success': False,
            'code': 99 
        })

        sys.exit(1)


    def __read(self) -> dict:
        """Reads message from STDIN and parses it"""
        try:
            return loads(input())
        except JSONDecodeError:
            raise InvalidJSONError()


    def __write(self, message: dict) -> None:
        """Writes message to STDOUT as JSON"""
        print(dumps(message))


    def __write_success(self, result: dict) -> None:
        """Writes a "success" message to STDOUT as JSON"""
        self.__write({
            "status": "success",
            "result": result
        })


    def __dispatch(self, message: dict) -> dict:
        """Dispatches a message, waits for the response, and returns that"""
        self.__write(message)
        return self.__read()



if __name__ == '__main__':
    Engine().run()
    