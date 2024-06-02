<?php
namespace App;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;

/**
 * Class meant to check Configs
 *
 * @category   Checks
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Check
{
    use AppTrait;

    /**
     * Supported HTTP methods of routes
     *
     * @var array
     */
    private $httpMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];

    /**
     * Routes folder
     *
     * @var string
     */
    private $configFolder = '/Config';

    /**
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        HttpRequest::init();
        HttpRequest::loadToken();
        HttpRequest::initSession();

        Env::$globalDB = Env::$defaultDbDatabase;
        Env::$clientDB = Env::$dbDatabase;

        $this->jsonObj = HttpResponse::getJsonObject();

        $this->process();
    }

    private function process()
    {
        $errors = [];
        $httpRoutes = [];
        $routesFolder = Constants::$DOC_ROOT . $this->configFolder . '/Routes';
        
        // $groupFolders = new \DirectoryIterator($routesFolder);
        // foreach ($groupFolders as $groupFolder) {
        //     if ($groupFolder->isDir() && !$groupFolder->isDot()) {
        //         $_routesFolder = $routesFolder . '/' . $groupFolder->getFilename();
        //         $httpRoutes[$groupFolder->getFilename()] = $this->processRoutes($_routesFolder, $errors);
        //     }
        // }
        
        $groupFolder = HttpRequest::$input['readOnlySession']['group_name'];
        $_routesFolder = $routesFolder . '/' . $groupFolder;
        $httpRoutes[$groupFolder] = $this->processRoutes($_routesFolder, $errors);

        if (!empty($errors)) {
            $this->jsonObj->addKeyValue('Results', $errors);
        } else {
            $this->processRoutesQueries($httpRoutes, $errors);
            if (!empty($errors)) {
                $this->jsonObj->addKeyValue('Results', $errors);
            }
        }
    }

    private function processRoutes($groupRoutesFolder, &$errors)
    {
        $httpRoutes = [];
        foreach ($this->httpMethods as $method) {
            $httpRoutes[$method] = [];
            $routeFileLocation =  $groupRoutesFolder . '/' . $method . 'routes.php';
            if (!file_exists($routeFileLocation)) {
                continue;
            }
            $routes = require $routeFileLocation;
            $errors[$routeFileLocation] = [];
            $this->checkRoutes($routes, $errors[$routeFileLocation]);
            if (empty($errors[$routeFileLocation])) {
                unset($errors[$routeFileLocation]);
                $route = '';
                $this->getRoutes($routes, $route, $httpRoutes[$method]);    
            }
        }
        return $httpRoutes;
    }

    private function checkConfigQueries($method, $__file__, &$errors)
    {
        $errors = [];

        $Constants = 'App\\Constants';
        $Env = 'App\\Env';
        $HttpRequest = 'App\\HttpRequest';

        if (file_exists($__file__)) {
            $sqlConfig = include $__file__;
            if ($method === 'GET') {
                $this->validateRead($sqlConfig, $errors);
            } else {
                $this->validateWrite($sqlConfig, $errors);
            }
        }
    }

    private function validateWrite(&$writeSqlConfig, &$errors, $useHierarchy = false)
    {
        $foundHierarchyData = false;
        foreach ($writeSqlConfig as $key => $value) {
            if (in_array($key, ['query', '__CONFIG__', '__WHERE__', 'insertId'])) {
                continue;
            }
            if ($useHierarchy && $key === '__SET__') {
                foreach ($value as $v) {
                    if ($v[0] === 'insertIdParams') {
                        $foundHierarchyData = true;
                    }
                }
                if (!$foundHierarchyData) {
                    $errors[] = 'Hierarchy usage not maintained.';
                }
            }
            if ($key === 'subQuery') {
                if (!$useHierarchy && isset($writeSqlConfig['useHierarchy'])) {
                    $useHierarchy = $writeSqlConfig['useHierarchy'];
                }        
                foreach ($writeSqlConfig[$key] as $module => &$_writeSqlConfig) {
                    $errors[$module] = [];
                    $_errors = &$errors[$module];
                    $this->validateWrite($_writeSqlConfig, $_errors, $useHierarchy);
                    if (empty($_errors)) {
                        unset($_errors);
                        unset($errors[$module]);
                    }
                }
            }
        }
    }

    private function validateRead(&$readSqlConfig, &$errors, $useHierarchy = false)
    {
        $foundHierarchyData = false;
        foreach ($readSqlConfig as $key => $value) {
            if (in_array($key, ['countQuery', 'query', '__CONFIG__', 'mode'])) {
                continue;
            }
            if ($useHierarchy && $key === '__WHERE__') {
                foreach ($value as $v) {
                    if ($v[0] === 'hierarchyData') {
                        $foundHierarchyData = true;
                        break;
                    }
                }
                if (!$foundHierarchyData) {
                    $errors[] = 'Hierarchy usage not maintained.';
                }
            }
            if ($key === 'subQuery') {
                if (!$useHierarchy && isset($readSqlConfig['useHierarchy'])) {
                    $useHierarchy = $readSqlConfig['useHierarchy'];
                }
                foreach ($readSqlConfig[$key] as $module => &$_readSqlConfig) {
                    $errors[$module] = [];
                    $_errors = &$errors[$module];
                    $this->validateRead($_readSqlConfig, $_errors, $useHierarchy);
                    if (empty($_errors)) {
                        unset($_errors);
                        unset($errors[$module]);
                    }
                }
            }
        }
    }

    private function processRoutesQueries($httpRoutes, &$errors)
    {
        foreach ($httpRoutes as $groupFolder => $httpRoute) {
            foreach ($httpRoute as $method => $routeDetailsArr) {
                foreach ($routeDetailsArr as $routeDetails) {
                    if (
                        $routeDetails['type'] === 'route' &&
                        file_exists($routeDetails['__file__'])
                    ) {
                        $errors[$routeDetails['__file__']] = [];
                        $_errors = &$errors[$routeDetails['__file__']];
                        $this->checkConfigQueries($method, $routeDetails['__file__'], $_errors);
                        if (empty($_errors)) {
                            unset($_errors);
                            unset($errors[$routeDetails['__file__']]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Create Routes list.
     *
     * @return void
     */
    private function checkRoutes(&$routes, &$errors)
    {
        $foundInt = false;
        $foundString = false;
        foreach ($routes as $key => &$arr) {
            if (in_array($key, ['config', '__file__']) && !is_array($arr)) {
                break;
            }
            if (strpos($key, '{') === 0) {
                $explode = explode('|', trim($key, '{}'));
                $explode0 = explode(':', $explode[0]);
                if (!(isset($explode0[1]) && in_array($explode0[1], ['int', 'string']))) {
                    if (!isset($explode0[1])) {
                        $errors[$key] = "Missing data type";
                    } else {
                        $errors[$key] = "Invalid data type: {$explode0[1]}";
                    }
                } else {
                    $var = 'found'.ucfirst(strtolower($explode0[1]));
                    if (!$$var) {
                        $$var = true;
                    } else {
                        $errors[$key] = "Datatype {$explode0[1]} used multiple times";
                    }
                }
                if (isset($explode[1])) {
                    $explode1 = explode(',', $explode[1]);
                    if ($var === 'foundInt') {
                        foreach ($explode1 as $val) {
                            if (!ctype_digit($val)) {
                                $errors[$key] = "Invalid int value: {$val}";
                            }
                        }
                    }
                    if ($var === 'foundString') {
                        foreach ($explode1 as $val) {
                            if (ctype_digit($val)) {
                                $errors[$key] = "Invalid string value: {$val}";
                            }
                        }
                    }
                }
            }
            if (is_array($arr)) {
                $this->checkRoutes($arr, $errors);
            }
        }
    }

    /**
     * Create Routes list.
     *
     * @return void
     */
    private function getRoutes(&$routes, $route, &$httpRoutes)
    {
        foreach ($routes as $key => &$r) {
            if (
                Env::$allowConfigRequest &&
                $key === 'config' &&
                $r === true
            ) {
                $httpRoutes[] = [
                    'r' => $route . '/' . $key,
                    'type' => 'config',
                    '__file__' => false
                ];
            }
            if ($key === '__file__') {
                $httpRoutes[] = [
                    'r' => $route,
                    'type' => 'route',
                    '__file__' => $r
                ];
            }
            if (is_array($r)) {
                $_route = $route . '/' . $key;
                $this->getRoutes($r, $_route, $httpRoutes);
            }
        }
    }

}
