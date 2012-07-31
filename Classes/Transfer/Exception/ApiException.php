<?php
namespace TYPO3\ElasticSearch\Transfer\Exception;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This exception type is intended to map any error output that was returned by ElasticSearch itself
 * If, for example, ElasticSearch returns {"error":"IndexMissingException[[foo_bar] missing]","status":404}
 * this exception is raised.
 */
class ApiException extends \TYPO3\ElasticSearch\Transfer\Exception {

}

?>