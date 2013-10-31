<?php
namespace Flowpack\ElasticSearch\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A type group that is for requests against multiple types, like in localhost:9200/foo/onetype,anothertype/_search....
 */
class TypeGroup extends AbstractType {

	/**
	 * @var array<AbstractType>
	 */
	protected $types = array();

	/**
	 * @param Index $index
	 * @param string $name
	 */
	public function __construct(Index $index, array $types) {
		parent::__construct($index);
		$this->types = $types;

		$names = array();
		foreach ($this->types as $type) {
			$names[] = $type->getName();
		}
		$this->name = implode(',', $names);
	}
}

