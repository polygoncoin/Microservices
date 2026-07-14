<?php

/**
 * Initiating API
 * php version 8.3
 *
 * @category  API
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Dropbox;
use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\Hook;
use Microservices\App\Http;
use Microservices\App\HttpStatus;
use Microservices\App\Supplement;

/**
 * Class to initialize api HTTP request
 * php version 8.3
 *
 * @category  API
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Api
{
	/**
	 * Hook object
	 *
	 * @var null|Hook
	 */
	private $hookObj = null;

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
	 * @return mixed
	 */
	public function process(): mixed
	{
		// Execute Pre Route Hook
		if (
			isset($this->httpObj)
			&& isset($this->httpObj->requestObj)
			&& isset($this->httpObj->requestObj->routeParserObj)
			&& isset($this->httpObj->requestObj->routeParserObj->routeHook)
			&& $this->httpObj->requestObj->routeParserObj->routeHook !== null
			&& is_array(
				value: $this->httpObj->requestObj->routeParserObj->routeHook
			)
		) {
			$preRouteHookArr = [];
			foreach ($this->httpObj->requestObj->routeParserObj->routeHook as $element => &$hookArr) {
				if (isset($hookArr['__PRE-ROUTE-HOOKS__'])) {
					$preRouteHookConfig = $hookArr['__PRE-ROUTE-HOOKS__'];
					if (
						count(
							value: $preRouteHookConfig
						) === 0
					) {
						continue;
					}

					 $indexCount = count(
						value: $preRouteHookConfig
					 );
					for ($index = 0; $index < $indexCount; $index++) {
						if (
							!in_array(
								needle: $preRouteHookConfig[$index],
								haystack: $preRouteHookArr,
								strict: true
							)
						) {
							$preRouteHookArr[] = $preRouteHookConfig[$index];
						}
					}
				}
			}
			if (
				count(
					value: $preRouteHookArr
				) > 0
			) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $preRouteHookArr
				);
			}
		}

		// Load Payloads
		if (
			!in_array(
				needle: $this->httpObj->requestObj->routeParserObj->routeEndingReservedKeyword,
				haystack: [
					Env::$explainRequestRouteKeyword,
					Env::$importSampleRequestRouteKeyword
				],
				strict: true
			)
		) {
			$this->httpObj->requestObj->loadPayload();
		}

		$class = null;
		$supplementClass = null;
		if (
			$this->checkSupplement(
				Env::$cronRequestRoutePrefix
			)
		) {
			$supplementClassFileName = ucfirst(
				string: $this->httpObj->requestObj->routeParserObj->routeElementArr[1]
			);
			$supplementClassFileLocation = Constant::$SUPPLEMENT_DIR
					. DIRECTORY_SEPARATOR . 'Cron'
					. DIRECTORY_SEPARATOR . $supplementClassFileName . '.php';

			if (
				file_exists(
					filename: $supplementClassFileLocation
				)
			) {
				$supplementClass = Constant::$SUPPLEMENT_NS . '\\Cron\\' . $supplementClassFileName;
			}
		} elseif (
			$this->checkSupplement(
				Env::$customRequestRoutePrefix
			)
		) {
			$supplementClassFileName = ucfirst(
				string: $this->httpObj->requestObj->routeParserObj->routeElementArr[1]
			);
			$supplementClassFileLocation = Constant::$SUPPLEMENT_DIR
					. DIRECTORY_SEPARATOR . 'Custom'
					. DIRECTORY_SEPARATOR . $supplementClassFileName . '.php';

			if (
				file_exists(
					filename: $supplementClassFileLocation
				)
			) {
				$supplementClass = Constant::$SUPPLEMENT_NS . '\\Custom\\' . $supplementClassFileName;
			}
		} elseif (
			$this->checkSupplement(
				Env::$uploadRequestRoutePrefix
			)
		) {
			$supplementClassFileName = ucfirst(
				string: $this->httpObj->requestObj->routeParserObj->routeElementArr[1]
			);
			$supplementClassFileLocation = Constant::$SUPPLEMENT_DIR
					. DIRECTORY_SEPARATOR . 'Upload'
					. DIRECTORY_SEPARATOR . $supplementClassFileName . '.php';

			if (
				file_exists(
					filename: $supplementClassFileLocation
				)
			) {
				$supplementClass = Constant::$SUPPLEMENT_NS . '\\Upload\\' . $supplementClassFileName;
			}
		} elseif (
			$this->checkSupplement(
				Env::$thirdPartyRequestRoutePrefix
			)
		) {
			$supplementClassFileName = ucfirst(
				string: $this->httpObj->requestObj->routeParserObj->routeElementArr[1]
			);
			$supplementClassFileLocation = Constant::$SUPPLEMENT_DIR
					. DIRECTORY_SEPARATOR . 'ThirdParty'
					. DIRECTORY_SEPARATOR . $supplementClassFileName . '.php';

			if (
				file_exists(
					filename: $supplementClassFileLocation
				)
			) {
				$supplementClass = Constant::$SUPPLEMENT_NS . '\\ThirdParty\\' . $supplementClassFileName;
			}
		} else {
			switch ($this->httpObj->httpReqData['server']['httpMethod']) {
				case Constant::$GET:
				case Constant::$QUERY:
					if (
						$this->checkSupplement(
							Env::$dropboxRequestRoutePrefix
						)
					) {
						$classFileName = ucfirst(
							string: $this->httpObj->requestObj->routeParserObj->routeElementArr[1]
						);
						$classFileLocation = Constant::$SUPPLEMENT_DIR
								. DIRECTORY_SEPARATOR . 'Dropbox'
								. DIRECTORY_SEPARATOR . $classFileName . '.php';

						if (
							file_exists(
								filename: $classFileLocation
							)
						) {
							$class = Constant::$SUPPLEMENT_NS . '\\Dropbox\\' . $classFileName;
						}
					} elseif (
						$this->checkSupplement(
							Env::$routesRequestRoute
						)
					) {
						$class = __NAMESPACE__ . '\\Route';
					} else {
						$class = __NAMESPACE__ . '\\Read';
					}
					break;
				case Constant::$POST:
				case Constant::$PUT:
				case Constant::$PATCH:
				case Constant::$DELETE:
					$class = __NAMESPACE__ . '\\Write';
					break;
			}
		}

		if ($supplementClass !== null) {
			$supplementObj = new Supplement(
				httpObj: $this->httpObj
			);
			if (
				$supplementObj->init(
					supplementClass: $supplementClass
				)
			) {
				$return = $supplementObj->process();
			}
		} elseif ($class !== null) {
			$api = new $class(
				httpObj: $this->httpObj
			);
			if ($api->init()) {
				$return = $api->process();
			}
		} else {
			throw new \Exception(
				message: 'API class file not found',
				code: HttpStatus::$NotFound
			);
		}

		// Execute Post Route Hook
		if (
			isset($this->httpObj)
			&& isset($this->httpObj->requestObj)
			&& isset($this->httpObj->requestObj->routeParserObj)
			&& isset($this->httpObj->requestObj->routeParserObj->routeHook)
			&& $this->httpObj->requestObj->routeParserObj->routeHook !== null
			&& is_array(
				value: $this->httpObj->requestObj->routeParserObj->routeHook
			)
		) {
			$postRouteHookArr = [];
			foreach ($this->httpObj->requestObj->routeParserObj->routeHook as $element => &$hookArr) {
				if (isset($hookArr['__POST-ROUTE-HOOKS__'])) {
					$postRouteHookConfig = $hookArr['__POST-ROUTE-HOOKS__'];
					if (
						count(
							value: $postRouteHookConfig
						) === 0
					) {
						continue;
					}
					
					$indexCount = count(
						value: $postRouteHookConfig
					);
					for ($index = 0; $index < $indexCount; $index++) {
						if (
							!in_array(
								needle: $postRouteHookConfig[$index],
								haystack: $postRouteHookArr,
								strict: true
							)
						) {
							$postRouteHookArr[] = $postRouteHookConfig[$index];
						}
					}
				}
			}
			if (
				count(
					value: $postRouteHookArr
				) > 0
			) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $postRouteHookArr
				);
			}
		}

		if (
			is_array(
				value: $return
			)
			&& count(
				value: $return
			) === 3
		) {
			return $return;
		}

		return true;
	}

	/**
	 * Process before collecting Payload
	 *
	 * @param string $supplementMode
	 *
	 * @return bool
	 */
	private function checkSupplement(
		$supplementMode
	): bool {
		return (
			$this->httpObj->requestObj->routeParserObj->routeStartingWithReservedKeywordFlag
			&& $this->httpObj->requestObj->routeParserObj->routeStartingReservedKeyword === $supplementMode
		);
	}

	/**
	 * Execute once done with api process function
	 *
	 * @return bool
	 */
	private function processAfterPayload(): bool
	{
		return true;
	}
}
