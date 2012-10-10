<?php
namespace TYPO3\ElasticSearch\Indexer\Object\Transform;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.ElasticSearch".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class TransformerFactory {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param string $annotatedTransformer Either a full qualified class name or a shortened one which is seeked in the current package.
	 *
	 * @throws \TYPO3\ElasticSearch\Exception
	 * @return \TYPO3\ElasticSearch\Indexer\Object\Transform\TransformerInterface
	 */
	public function create($annotatedTransformer) {
		if (!class_exists($annotatedTransformer)) {
			$annotatedTransformer = 'TYPO3\ElasticSearch\Indexer\Object\Transform\\' . $annotatedTransformer . 'Transformer';
		}
		$transformer = $this->objectManager->get($annotatedTransformer);
		if (!$transformer instanceof \TYPO3\ElasticSearch\Indexer\Object\Transform\TransformerInterface) {
			throw new \TYPO3\ElasticSearch\Exception(sprintf('The transformer instance "%s" does not implement the TransformerInterface.', $annotatedTransformer), 1339598316);
		}

		return $transformer;
	}
}

?>