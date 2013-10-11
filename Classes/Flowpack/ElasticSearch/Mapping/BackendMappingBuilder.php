<?php
namespace Flowpack\ElasticSearch\Mapping;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.ElasticSearch".*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Domain\Model;

/**
 * This collects mappings from a live instance
 *
 * @Flow\Scope("singleton")
 */
class BackendMappingBuilder {

	/**
	 * @var \Flowpack\ElasticSearch\Domain\Model\Client
	 */
	protected $client;

	/**
	 * Gets populated to an array during buildMappingInformation(). Contains "empty" mappings that have no type
	 * attached, in order to display these. If this stays NULL, the buildMappingInformation() has not yet been run.
	 * @var null|array
	 */
	protected $indicesWithoutTypeInformation = NULL;

	/**
	 * Builds a Mapping collection from the annotation sources that are present
	 *
	 * @throws \Flowpack\ElasticSearch\Exception
	 * @return \Flowpack\ElasticSearch\Mapping\MappingCollection<\Flowpack\ElasticSearch\Domain\Model\Mapping>
	 */
	public function buildMappingInformation() {
		if (!$this->client instanceof Model\Client) {
			throw new \Flowpack\ElasticSearch\Exception('No client was given for mapping retrieval. Set a client BackendMappingBuilder->setClient().', 1339678111);
		}

		$this->indicesWithoutTypeInformation = array();

		$response = $this->client->request('GET', '/_mapping');
		$mappingInformation = new MappingCollection(MappingCollection::TYPE_BACKEND);
		$mappingInformation->setClient($this->client);
		foreach ($response->getTreatedContent() AS $indexName => $indexSettings) {
			$index = new Model\Index($indexName);
			if (empty($indexSettings)) {
				$this->indicesWithoutTypeInformation[] = $indexName;
			}
			foreach ($indexSettings AS $typeName => $typeSettings) {
				$type = new Model\GenericType($index, $typeName);
				$mapping = new Model\Mapping($type);
				if (isset($typeSettings['properties'])) {
					foreach ($typeSettings['properties'] AS $propertyName => $propertySettings) {
						foreach ($propertySettings AS $key => $value) {
							$mapping->setPropertyByPath(array($propertyName, $key), $value);
						}
					}
				}
				$mappingInformation->add($mapping);
			}
		}

		return $mappingInformation;
	}

	/**
	 * @param \Flowpack\ElasticSearch\Domain\Model\Client $client
	 */
	public function setClient(Model\Client $client) {
		$this->client = $client;
	}

	/**
	 * @throws \Flowpack\ElasticSearch\Exception
	 * @return array
	 */
	public function getIndicesWithoutTypeInformation() {
		if ($this->indicesWithoutTypeInformation === NULL) {
			throw new \Flowpack\ElasticSearch\Exception('For getting the indices having no mapping information attached, BackendMappingBuilder->buildMappingInformation() has to be run first.', 1339751812);
		}
		return $this->indicesWithoutTypeInformation;
	}
}

?>