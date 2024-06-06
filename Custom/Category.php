<?php
namespace Custom;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Servers\Database\Database;
use App\Validation\Validator;

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
    public $jsonObj = null;

    /**
     * Initialize
     *
     * @return bool
     */
    public function init()
    {
        $this->db = Database::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();

        return true;
    }

    /**
     * Process
     *
     * @return void
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
        $this->jsonObj->addKeyValue('Results', $rows);
        
        return true;
    }
}
