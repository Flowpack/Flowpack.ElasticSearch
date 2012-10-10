<?php
namespace TYPO3\ElasticSearch\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.ElasticSearch".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;

/**
 * An abstract document type. Implement your own or use the GenericType provided with this package.
 */
class GenericType extends AbstractType {

	/**
	 * @param \TYPO3\ElasticSearch\Domain\Model\Index $index
	 * @param string $name
	 */
	public function __construct(Index $index, $name) {
		parent::__construct($index, $name);
	}
}

?>