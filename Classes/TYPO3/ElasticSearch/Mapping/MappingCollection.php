<?php
namespace TYPO3\ElasticSearch\Mapping;

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
 */
class MappingCollection extends \Doctrine\Common\Collections\ArrayCollection {
	const TYPE_BACKEND = 'backend';
	const TYPE_ENTITY = 'entity';

	/**
	 * One of self::TYPE_* constants
	 * @var string
	 */
	protected $type;

	/**
	 * @var \TYPO3\ElasticSearch\Domain\Model\Client
	 */
	protected $client;

	/**
	 * @param string $type
	 */
	public function __construct($type = NULL) {
		parent::__construct(array());
		$this->type = $type;
	}

	/**
	 * Returns a new collection of mappings of this collection that are not member of the $complementCollection.
	 *
	 * @param MappingCollection $complementCollection	 *
	 * @return \TYPO3\ElasticSearch\Mapping\MappingCollection
	 */
	public function diffAgainstCollection(MappingCollection $complementCollection) {
		$returnMappings = new \TYPO3\ElasticSearch\Mapping\MappingCollection();
		foreach ($this AS $entityMapping) {
			/** @var $entityMapping \TYPO3\ElasticSearch\Domain\Model\Mapping */
			$mapping = new \TYPO3\ElasticSearch\Domain\Model\Mapping(clone $entityMapping->getType());
			$saveMapping = FALSE;
			foreach ($entityMapping->getProperties() AS $propertyName => $propertySettings) {
				foreach ($propertySettings AS $entitySettingKey => $entitySettingValue) {
					$backendSettingValue = $complementCollection->getMappingSetting($entityMapping, $propertyName, $entitySettingKey);
					if ($entitySettingValue !== $backendSettingValue) {
						$mapping->setPropertyByPath(array($propertyName, $entitySettingKey), $entitySettingValue);
						$saveMapping = TRUE;
					}
				}
			}
			if ($saveMapping) {
				$returnMappings->add($mapping);
			}
		}

		return $returnMappings;
	}

	/**
	 * Tells whether a member of this collection has a specific index/type/property settings value
	 *
	 * @param \TYPO3\ElasticSearch\Domain\Model\Mapping $inquirerMapping
	 * @param string $propertyName
	 * @param $settingKey
	 *
	 * @return mixed
	 */
	public function getMappingSetting(\TYPO3\ElasticSearch\Domain\Model\Mapping $inquirerMapping, $propertyName, $settingKey) {
		foreach ($this AS $memberMapping) {
			/** @var $memberMapping \TYPO3\ElasticSearch\Domain\Model\Mapping */
			if ($inquirerMapping->getType()->getName() === $memberMapping->getType()->getName()
				&& $inquirerMapping->getType()->getIndex()->getName() === $memberMapping->getType()->getIndex()->getName()) {
					return $memberMapping->getPropertyByPath(array($propertyName, $settingKey));
			}
		}
		return NULL;
	}

	/**
	 * @param \TYPO3\ElasticSearch\Domain\Model\Client $client
	 */
	public function setClient(\TYPO3\ElasticSearch\Domain\Model\Client $client) {
		$this->client = $client;
	}

	/**
	 * @return \TYPO3\ElasticSearch\Domain\Model\Client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}

?>