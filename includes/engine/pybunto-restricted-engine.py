import json
from RestrictedPython import compile_restricted_exec, safe_builtins


def readInput():
    raw_input = input()
    parsed_input = json.loads(raw_input)

    # TODO: Error handling

    return (parsed_input["source"], parsed_input["name"], parsed_input["args"])


def dispatch(info):
    print(json.dumps(info))


def compileModule(module_source):
    return compile_restricted_exec(
        source=module_source,
        filename='<module code>',
        dont_inherit=True
    )


def execModule(module_code):
    global_environment = {'__builtins__': safe_builtins}
    local_environment = {}

    exec(module_code, global_environment, local_environment)

    return local_environment


def getFunction(module_code, function_name):
    environment = execModule(module_code)
  
    if environment[function_name] == None:
        # Throw exception or something
        pass

    return environment[function_name]


def invokeFunction(module_code, function_name, function_args):
    return getFunction(module_code, function_name)(**function_args)


def main():
    (module_source, function_name, function_args) = readInput()
    (module_code, errors, warnings, used_names) = compileModule(module_source)
    
    # TODO: Error handling

    result = invokeFunction(module_code, function_name, function_args)

    dispatch({
        "status": "success",
        "result": result
    })


if __name__ == '__main__':
    main()
