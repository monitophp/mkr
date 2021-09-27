<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;
use MonitoLib\Functions;

class Route
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function create(array $table, \stdClass $options)
    {
        $namespace  = $options->namespace;
        $url        = $table['url'];
        $routesFile = App::getRoutesPath() . str_replace('\\', '_', strtolower($namespace)) . '.php';

        $lines = [];

        if (file_exists($routesFile)) {
            $lines = file($routesFile, FILE_IGNORE_NEW_LINES);
        }

        $routes[] = "Router::get('$url', '\\$namespace\\Controller\\{$table['class']}@get');";
        $routes[] = "Router::get('$url/:{[0-9]{1,}}', '\\$namespace\\Controller\\{$table['class']}@get');";

        if ($table['type'] === 'table') {
            $routes[] = "Router::post('$url', '\\$namespace\\Controller\\{$table['class']}@create');";
            $routes[] = "Router::put('$url/:{[0-9]{1,}}', '\\$namespace\\Controller\\{$table['class']}@update');";
            $routes[] = "Router::delete('$url', '\\$namespace\\Controller\\{$table['class']}@delete');";
            $routes[] = "Router::delete('$url/:{[0-9]{1,}}', '\\$namespace\\Controller\\{$table['class']}@delete');";
        }

        foreach ($routes as $route) {
            if (!in_array($route, $lines)) {
                $lines[] = $route;
            }
        }

        $f = "<?php\n"
            . "use \MonitoLib\Router;\n"
            . "\n"
            . '// ' . __CLASS__ . ' v' . self::VERSION . ' ' . App::now() . "\n"
            . "\n";

        foreach ($lines as $line) {
            if (substr($line, 0, 6) === 'Router') {
                $f .= "$line\n";
            }
        }

        return $f;
    }
}
