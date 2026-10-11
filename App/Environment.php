<?php

/**
 * Environment
 * php version 8.3
 *
 * @category  Environment
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Constant;
use Microservices\App\HttpStatus;

/**
 * Environment
 * php version 8.3
 *
 * @category  Environment
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Environment
{
    public $PUBLIC_DOMAIN_NAME = null;
    public $PRIVATE_WEB_DOMAIN_NAME = null;
    public $PRIVATE_API_DOMAIN_NAME = null;

    public $OUTPUT_PERFORMANCE_STATS = null;
    public $DISABLES_VIA_PROXIES = null;
    public $CUSTOMER_CONFIG_DIRECTORY = null;

    public $SECRET = null;
    public $PAYLOAD_IN_RESPONSE = null;

    public $ENABLE_RELOAD_CACHE = null;
    public $RELOAD_KEYWORD = null;
    public $RELOAD_CACHE_CIDR = null;

    public $ENABLE_INPUT_REPRESENTATION_IN_QUERY_STRING = null;
    public $ENABLE_OUTPUT_REPRESENTATION_IN_QUERY_STRING = null;

    public $ENABLE_PAYLOAD_IN_RESPONSE = null;

    public $ENABLE_QUERY_CACHE_FOR_PUBLIC = null;
    public $ENABLE_QUERY_CACHE_FOR_PRIVATE_WEB = null;
    public $ENABLE_QUERY_CACHE_FOR_PRIVATE_API = null;

    public $ENABLE_PUBLIC = null;
    public $ENABLE_PRIVATE_WEB = null;
    public $ENABLE_PRIVATE_API = null;

    public $TOKEN_LIFETIME = null;
    public $SESSION_LIFETIME = null;

    public $ENABLE_CIDR = null;
    public $ENABLE_CRON = null;
    public $ENABLE_CUSTOM = null;
    public $ENABLE_DROPBOX = null;
    public $ENABLE_DOWNLOAD = null;
    public $ENABLE_EXPLAIN = null;
    public $ENABLE_IMPORT = null;
    public $ENABLE_IMPORT_SAMPLE = null;
    public $ENABLE_ROUTES = null;
    public $ENABLE_THIRDPARTY = null;
    public $ENABLE_UPLOAD = null;

    public $ENABLE_RATE_LIMITING = null;
    public $ENABLE_RATE_LIMITING_IP = null;
    public $ENABLE_RATE_LIMITING_USER_LOGIN = null;
    public $ENABLE_RATE_LIMITING_SUCCESSFULL_LOGIN_PER_USER = null;
    public $ENABLE_RATE_LIMITING_LOGGED_IN_USER = null;
    public $ENABLE_RATE_LIMITING_LOGGED_IN_USER_PER_IP = null;

    public $CACHE_MODE = null;
    public $CACHE_HOST = null;
    public $CACHE_PORT = null;
    public $CACHE_USER = null;
    public $CACHE_PASSWORD = null;
    public $CACHE_DB = null;
    public $CACHE_TABLE = null;

    public $MASTER_DB_MODE = null;
    public $MASTER_DB_HOST = null;
    public $MASTER_DB_PORT = null;
    public $MASTER_DB_USER = null;
    public $MASTER_DB_PASSWORD = null;
    public $MASTER_DB_NAME = null;
    public $MASTER_DB_PLACEHOLDER = null;

    public $SLAVE_DB_MODE = null;
    public $SLAVE_DB_HOST = null;
    public $SLAVE_DB_PORT = null;
    public $SLAVE_DB_USER = null;
    public $SLAVE_DB_PASSWORD = null;
    public $SLAVE_DB_NAME = null;
    public $SLAVE_DB_PLACEHOLDER = null;

    public $QUERY_CACHE_MODE = null;
    public $QUERY_CACHE_HOST = null;
    public $QUERY_CACHE_PORT = null;
    public $QUERY_CACHE_USER = null;
    public $QUERY_CACHE_PASSWORD = null;
    public $QUERY_CACHE_DB = null;
    public $QUERY_CACHE_TABLE = null;

    public $SESSION_STORE_MODE = null;

    public $SESSION_STORE_PATH = null;

    public $SESSION_COOKIE_NAME = null;
    public $SESSION_COOKIE_PATH = null;
    public $SESSION_COOKIE_SECURE = null;
    public $SESSION_COOKIE_HTTPONLY = null;
    public $SESSION_COOKIE_SAMESITE = null;

    public $SESSION_ENCRYPTION_PASS_PHRASE = null;
    public $SESSION_ENCRYPTION_IV = null;

    public $SESSION_HOST = null;
    public $SESSION_PORT = null;
    public $SESSION_USER = null;
    public $SESSION_PASSWORD = null;
    public $SESSION_DB = null;
    public $SESSION_TABLE = null;

    public $SESSION_DATA_COOKIE_NAME = null;

    public $INPUT_REPRESENTATION = null;
    public $OUTPUT_REPRESENTATION = null;

    public $DEFAULT_PER_PAGE_COUNT = null;
    public $MAX_PER_PAGE_COUNT = null;

    public $APPEND_SUPPLEMENT_FUNCTION_KEYWORD = null;

    public $EXPLAIN_KEYWORD = null;
    public $IMPORT_CSV_KEYWORD = null;
    public $IMPORT_SAMPLE_KEYWORD = null;

    public $DROPBOX_KEYWORD = null;
    public $CRON_KEYWORD = null;
    public $CUSTOM_KEYWORD = null;
    public $THIRD_PARTY_KEYWORD = null;
    public $UPLOAD_KEYWORD = null;

    public $DOWNLOAD_CLIENT_LOCATION = null;

    public $RATE_LIMITING_PER_IP_PREFIX = null;
    public $RATE_LIMITING_ROUTE_PREFIX = null;
    public $RATE_LIMITING_ROUTE_IP_PREFIX = null;
    public $RATE_LIMITING_GROUP_PREFIX = null;
    public $RATE_LIMITING_GROUP_IP_PREFIX = null;
    public $RATE_LIMITING_USER_PREFIX = null;
    public $RATE_LIMITING_USER_IP_PREFIX = null;
    public $RATE_LIMITING_LOGIN_PREFIX = null;

	/**
	 * Constructor
	 * 
	 * @param string $envFile Enviroment file name
	 */
	public function __construct($envFile)
	{
        $envDataArray = parse_ini_file(
            filename: ROOT . DIRECTORY_SEPARATOR . $envFile
        );
        foreach ($envDataArray as $envVarName => $envVarValue) {
            if ($envVarName === 'OUTPUT_REPRESENTATION') {
                $this->OUTPUT_REPRESENTATION = [
                    'OUTPUT_REPRESENTATION' => $envVarValue,
                    'OUTPUT_REPRESENTATION_FILE' => Constant::$FALSE
                ];
            } else {
                $this->$envVarName = $envVarValue;
            }
        }
	}
}
