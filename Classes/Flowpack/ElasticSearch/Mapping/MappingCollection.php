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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 */
class MappingCollection extends \Doctrine\Common\Collections\ArrayCollection
{
    const TYPE_BACKEND = 'backend';
    const TYPE_ENTITY = 'entity';

    /**
     * One of self::TYPE_* constants
     *
     * @var string
     */
    protected $type;

    /**
     * @var \Flowpack\ElasticSearch\Domain\Model\Client
     */
    protected $client;

    /**
     * @param string $type
     */
    public function __construct($type = null)
    {
        parent::__construct(array());
        $this->type = $type;
    }

    /**
     * Returns a new collection of mappings of this collection that are not member of the $complementCollection.
     *
     * @param MappingCollection $complementCollection
     * @return \Flowpack\ElasticSearch\Mapping\MappingCollection
     */
    public function diffAgainstCollection(MappingCollection $complementCollection)
    {
        $returnMappings = new \Flowpack\ElasticSearch\Mapping\MappingCollection();
        foreach ($this as $entityMapping) {
            /** @var $entityMapping \Flowpack\ElasticSearch\Domain\Model\Mapping */
            $mapping = new \Flowpack\ElasticSearch\Domain\Model\Mapping(clone $entityMapping->getType());
            $saveMapping = false;
            foreach ($entityMapping->getProperties() as $propertyName => $propertySettings) {
                foreach ($propertySettings as $entitySettingKey => $entitySettingValue) {
                    $backendSettingValue = $complementCollection->getMappingSetting($entityMapping, $propertyName, $entitySettingKey);
                    if ($entitySettingValue !== $backendSettingValue) {
                        $mapping->setPropertyByPath(array($propertyName, $entitySettingKey), $entitySettingValue);
                        $saveMapping = true;
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
     * @param \Flowpack\ElasticSearch\Domain\Model\Mapping $inquirerMapping
     * @param string $propertyName
     * @param $settingKey
     *
     * @return mixed
     */
    public function getMappingSetting(\Flowpack\ElasticSearch\Domain\Model\Mapping $inquirerMapping, $propertyName, $settingKey)
    {
        foreach ($this as $memberMapping) {
            /** @var $memberMapping \Flowpack\ElasticSearch\Domain\Model\Mapping */
            if ($inquirerMapping->getType()->getName() === $memberMapping->getType()->getName()
                && $inquirerMapping->getType()->getIndex()->getName() === $memberMapping->getType()->getIndex()->getName()) {
                return $memberMapping->getPropertyByPath(array($propertyName, $settingKey));
            }
        }

        return null;
    }

    /**
     * @param \Flowpack\ElasticSearch\Domain\Model\Client $client
     */
    public function setClient(\Flowpack\ElasticSearch\Domain\Model\Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return \Flowpack\ElasticSearch\Domain\Model\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
