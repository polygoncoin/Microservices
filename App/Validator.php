<?php

/**
 * Validator
 * php version 8.3
 *
 * @category  Validator
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\Http;
use Microservices\www\Validation\CustomerValidator;
use Microservices\www\Validation\GlobalValidator;
use Microservices\www\Validation\ValidatorInterface;

/**
 * Validator
 * php version 8.3
 *
 * @category  Validator
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Validator
{
	/**
	 * Validator object
	 *
	 * @var null|ValidatorInterface
	 */
	private $validatorObj = null;

	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	private $httpObj = null;

	/**
	 * Constructor
	 *
	 * @param Http $httpObj
	 */
	public function __construct(
		Http &$httpObj
	) {
		$this->httpObj = &$httpObj;
		if ($this->httpObj->httpRequestObj->customerDbObj->dbServerDatabase === Env::$gDbServerDatabase) {
			$this->validatorObj = new GlobalValidator(
				httpObj: $this->httpObj
			);
		} else {
			$this->validatorObj = new CustomerValidator(
				httpObj: $this->httpObj
			);
		}
	}

	/**
	 * Validate payload
	 *
	 * @param array $validationConfig Validation configuration
	 *
	 * @return array
	 */
	public function validate(
		&$validationConfig
	): array {
		if (
			isset(($this->httpObj->httpRequestObj->session['requiredFieldArr']))
			&& count(
				value: $this->httpObj->httpRequestObj->session['requiredFieldArr']
			) > 0
		) {
			if (
				([$isValidData, $errorArr] = $this->validateRequired())
				&& !$isValidData
			) {
				return [$isValidData, $errorArr];
			}
		}

		return $this->validatorObj->validate(
			validationConfig: $validationConfig
		);
	}

	/**
	 * Validate required payload
	 *
	 * @return array
	 */
	private function validateRequired(): array
	{
		$isValidData = true;
		$errorArr = [];
		// Required fields payload validation
		if (!empty($this->httpObj->httpRequestObj->session['requiredFieldArr']['payload'])) {
			foreach ($this->httpObj->httpRequestObj->session['requiredFieldArr']['payload'] as $fetchFromData) {
				if (
					!in_array(
						needle: $fetchFromData,
						haystack: $this->httpObj->httpRequestObj->session['payload'],
						strict: Constant::$TRUE
					)
				) {
					$errorArr[] = 'Missing required payload: ' . $fetchFromData;
					$isValidData = false;
				}
			}
		}

		return [$isValidData, $errorArr];
	}
}
