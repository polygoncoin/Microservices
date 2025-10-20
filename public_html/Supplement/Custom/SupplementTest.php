<?php

/**
 * CustomAPI
 * php version 8.3
 *
 * @category  CustomAPI
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\public_html\Supplement\Custom;

use Microservices\App\Common;
use Microservices\App\Servers\Containers\Sql\AbstractDatabase;
use Microservices\public_html\Supplement\Custom\CustomInterface;
use Microservices\public_html\Supplement\Custom\CustomTrait;

/**
 * CustomAPI Supplement Test
 * php version 8.3
 *
 * @category  CustomAPI_SupplementTest
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class SupplementTest implements CustomInterface
{
    use CustomTrait;

    /**
     * Database object
     *
     * @var null|AbstractDatabase
     */
    public $db = null;

    /**
     * Common object
     *
     * @var null|Common
     */
    private $c = null;

    /**
     * Constructor
     *
     * @param Common $common Common object Common object
     */
    public function __construct(Common &$common)
    {
        $this->c = &$common;
        $this->c->req->db = $this->c->req->setDbConnection(fetchFrom: $fetchFrom = 'Slave');
        $this->db = &$this->c->req->db;
    }

    /**
     * Initialize
     *
     * @return bool
     */
    public function init(): bool
    {
        return true;
    }

    /**
     * Process
     *
     * @param array $payload Payload
     *
     * @return array
     */
    public function process(array $payload = []): array
    {
        return $payload;
    }

    /**
     * Process Sub
     *
     * @param array $payload Payload
     *
     * @return array
     */
    public function processSub(array $payload = []): array
    {
        return $payload;
    }
}
