<?php
namespace Microservices\Custom;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\Servers\Database\Database;

/**
 * Class to initialize DB Read operation
 *
 * This class process the GET api request
 *
 * @category   Category
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Category
{
    /**
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

    /**
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonEncode = null;

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        $this->db = Database::getObject();
        $this->jsonEncode = HttpResponse::getJsonObject();
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $sql = 'SELECT * FROM category WHERE is_deleted = :is_deleted AND parent_id = :parent_id';
        $sqlParams = [
            ':is_deleted' => 'No',
            ':parent_id' => 0,
        ];
        $this->db->execDbQuery($sql, $sqlParams);
        $rows = $this->db->fetchAll();
        $this->db->closeCursor();
        $this->jsonEncode->addKeyValue('Results', $rows);
        return HttpResponse::isSuccess();
    }
}
