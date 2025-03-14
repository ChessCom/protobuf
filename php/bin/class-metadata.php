<?php

require __DIR__ . '/../vendor/autoload.php';

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $errorTypes = [
        E_USER_ERROR => "USER ERROR",
        E_USER_WARNING => "USER WARNING",
        E_USER_NOTICE => "USER NOTICE",
        E_WARNING => "WARNING",
        E_NOTICE => "NOTICE",
        E_CORE_WARNING => "CORE WARNING",
        E_COMPILE_WARNING => "COMPILE WARNING",
        E_DEPRECATED => "DEPRECATED",
        E_USER_DEPRECATED => "USER DEPRECATED",
    ];

    if (isset($errorTypes[$errno])) {
        $errorType = $errorTypes[$errno];
        $messageMessage = sprintf(
            "%s: %s in %s on line %d",
            $errorType,
            $errstr,
            $errfile,
            $errline
        );

        fwrite(STDERR, $messageMessage . "\n");

        return true;
    }

    throw new ErrorException($messageMessage, 0, $errno, $errfile, $errline);
});

function extractClassMetadata($className): array
{
    $class = new ReflectionClass($className);
    $classMetadata = [
        'methods' => [],
        'name' => $class->getName(),
        'is_final' => $class->isFinal(),
        'is_abstract' => $class->isAbstract(),
        'namespace' => $class->getNamespaceName(),
        'interfaces' => $class->getInterfaceNames(),
        'extends' => $class->getParentClass() ? $class->getParentClass()->getName() : null,
    ];

    foreach ($class->getMethods() as $method) {
        $methodMetadata = [
            'name' => $method->getName(),
            'parameters' => [],
            'modifiers' => Reflection::getModifierNames($method->getModifiers()),
        ];

        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $methodMetadata['parameters'][] = [
                'name' => $parameter->getName(),
                'is_optional' => $parameter->isOptional(),
                'type' => $parameter->hasType() ? (string) $parameter->getType() : null,
            ];
        }
        $classMetadata['methods'][$method->getName()] = $methodMetadata;
    }

    return $classMetadata;
}

function extractNamespaceMetadata($path)
{
    $classes = [];
    $pathLength = strlen(rtrim($path, DIRECTORY_SEPARATOR)) + 1;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($iterator as $file) {
        if ($file->isDir() || substr($file->getFilename(), -4) !== '.php') {
            continue;
        }

        $relativePath = substr($file->getPathname(), $pathLength);
        $namespaceFormat = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        try {
            $className = str_replace('.php', '', $namespaceFormat);
            $classMeta = extractClassMetadata($className);
            $className = $classMeta['name'];

            $classes[$className] = $classMeta;
        } catch (ReflectionException $e) {
            continue;
        }
    }

    return [
        'path' => $path,
        'classes' => $classes,
        'extensions' => get_loaded_extensions(),
    ];
}

$options = getopt("o:s:");
$output = isset($options['o']) ? $options['o'] : '-';
$path = isset($options['s']) ? $options['s'] : './tmp';

if (!is_dir($path)) {
    fwrite(STDERR, "Error: Invalid source dir '$path'\n");
    fwrite(STDERR, "Usage: php ./bin/class-metadata.php -s <source_path> [-o <output_file>]\n");
    exit(1);
}

try {
    $metadata = extractNamespaceMetadata($path);
    $json = json_encode($metadata, JSON_PRETTY_PRINT);

    if ($output !== '-') {
        file_put_contents($output, $json);
        exit(0);
    }

    fwrite(STDOUT, $json);
} catch (\Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Stack Trace:\n" . $e->getTraceAsString() . "\n");
    exit(1);
}
