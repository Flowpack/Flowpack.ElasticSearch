<?php
namespace Flowpack\ElasticSearch\Command;

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
 * Provides CLI features for mapping handling
 *
 * @Flow\Scope("singleton")
 */
class MappingCommandController extends \TYPO3\Flow\Cli\CommandController
{
    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Mapping\EntityMappingBuilder
     */
    protected $entityMappingBuilder;

    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Mapping\BackendMappingBuilder
     */
    protected $backendMappingBuilder;

    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Domain\Factory\ClientFactory
     */
    protected $clientFactory;

    /**
     * Shows the status of the current mapping...
     *
     * @param string $clientName The client name for the configuration. Defaults to the default client configured.
     * @return void
     */
    public function showStatusCommand($clientName = null)
    {
        $entityMappingCollection = $this->entityMappingBuilder->buildMappingInformation();
        $entityMappingCollection = $this->buildArrayFromMappingCollection($entityMappingCollection);

        $client = $this->clientFactory->create($clientName);
        $this->backendMappingBuilder->setClient($client);
        $backendMappingCollection = $this->backendMappingBuilder->buildMappingInformation();
        $backendMappingCollection = $this->buildArrayFromMappingCollection($backendMappingCollection);

        $this->printLegend();
        $this->outputFormatted('<b>Mapping status:</b>');
        $this->outputFormatted('<b>---------------</b>');

        $mergedMappingCollection = array_merge_recursive($entityMappingCollection, $backendMappingCollection);
        foreach ($mergedMappingCollection as $indexName => $typeSet) {
            $this->outputFormatted('index %s:', array($this->markupDiffValue(isset($entityMappingCollection[$indexName]) ? $indexName : null, isset($backendMappingCollection[$indexName]) ? $indexName : null)));
            foreach ($typeSet as $typeName => $mappingSet) {
                $propertiesSet = $mappingSet['properties'];
                $this->outputFormatted('type %s:', array($this->markupDiffValue(isset($entityMappingCollection[$indexName][$typeName]) ? $typeName : null, isset($backendMappingCollection[$indexName][$typeName]) ? $typeName : null)), 4);
                foreach ($propertiesSet as $propertyName => $properties) {
                    $entityProperties = \TYPO3\Flow\Utility\Arrays::getValueByPath($entityMappingCollection, array($indexName, $typeName, 'properties', $propertyName));
                    $backendProperties = \TYPO3\Flow\Utility\Arrays::getValueByPath($backendMappingCollection, array($indexName, $typeName, 'properties', $propertyName));

                    $this->outputFormatted('property %s:', array($this->markupDiffValue($entityProperties ? $propertyName : null, $backendProperties ? $propertyName : null)), 8);
                    foreach ($properties as $key => $value) {
                        $keyMarkup = $this->markupDiffValue(isset($entityProperties[$key]) ? $key : null, isset($backendProperties[$key]) ? $key : null);
                        $valueMarkup = $this->markupDiffValue(isset($entityProperties[$key]) ? $entityProperties[$key] : null, isset($backendProperties[$key]) ? $backendProperties[$key] : null);
                        $this->outputFormatted("%s : %s", array($keyMarkup, $valueMarkup), 12);
                    }
                }
                $this->outputLine();
            }
            $this->outputLine();
        }

        if (count($indicesWithoutTypeInformation = $this->backendMappingBuilder->getIndicesWithoutTypeInformation())) {
            $this->outputFormatted("\x1b[43mNotice:\x1b[0m The following indices are present in the backend's mapping but having no type configuration, can hence be regarded as garbage:");
            foreach ($indicesWithoutTypeInformation as $indexName) {
                $this->outputFormatted('* %s', array($indexName), 4);
            }
        }
    }

    /**
     * This command will adjust the backend's mapping to the mapping the entity status prescribes.
     *
     * @param string $clientName The client name for the configuration. Defaults to the default client configured.
     * @return void
     */
    public function convergeCommand($clientName = null)
    {
        $client = $this->clientFactory->create($clientName);

        $entityMappingCollection = $this->entityMappingBuilder->buildMappingInformation();
        $this->backendMappingBuilder->setClient($client);
        $backendMappingCollection = $this->backendMappingBuilder->buildMappingInformation();

        $additiveMappings = $entityMappingCollection->diffAgainstCollection($backendMappingCollection);
        /** @var $mapping \Flowpack\ElasticSearch\Domain\Model\Mapping */
        foreach ($additiveMappings as $mapping) {
            $index = $mapping->getType()->getIndex();
            $index->setClient($client);
            if (!$index->exists()) {
                $this->outputFormatted('Index <b>%s</b> does not exist', array($index->getName()));
                continue;
            }
            $this->outputLine('Attempt to apply properties to %s/%s: %s... ', array(
                $index->getName(),
                $mapping->getType()->getName(),
                print_r($mapping->getProperties(), true)
            ));
            $response = $mapping->apply();
            if ($response->getStatusCode() === 200) {
                $this->outputFormatted('<b>OK</b>');
            } else {
                $this->outputFormatted('<b>NOT OK</b>, response code was %d, response body was: %s', array($response->getStatusCode(), $response->getOriginalResponse()->getContent()), 4);
            }
        }
        if (0 === $additiveMappings->count()) {
            $this->outputLine('No mappings were to be applied.');
        }
    }

    /**
     * @return void
     */
    protected function printLegend()
    {
        $legendText = "
" . $this->markupDiffValue(null, 'something') . " defined in backend, but not in entities
" . $this->markupDiffValue('something', null) . " defined in entities, but not in backend
" . $this->markupDiffValue('something', 'something') . " defined both in entities and backend, all OK
" . $this->markupDiffValue('something', 'different') . " different in entities and backend
";
        $this->outputFormatted('<b>Legend:</b>');
        $this->outputFormatted($legendText, array(), 4);
    }

    /**
     * @param mixed $entityValue
     * @param mixed $backendValue
     * @return string
     */
    protected function markupDiffValue($entityValue, $backendValue)
    {
        $markup = '';
        if ($entityValue === null || $backendValue === null || $entityValue === $backendValue) {
            $markup .= "\x1b[" . ($entityValue ? '31' : '30') . ';' . ($backendValue ? '42' : '0') . 'm';
            if (is_array($entityValue)) {
                $entityValue = var_export($entityValue, true);
            }
            if (is_array($backendValue)) {
                $backendValue = var_export($backendValue, true);
            }
            $markup .= $entityValue ? : $backendValue;
            $markup .= "\x1b[0m";
        } else {
            if (is_array($entityValue)) {
                $entityValue = var_export($entityValue, true);
            }
            if (is_array($backendValue)) {
                $backendValue = var_export($backendValue, true);
            }
            $markup .= "\x1b[31m" . $entityValue . "\x1b[0m";
            $markup .= "\x1b[30;42m" . $backendValue . "\x1b[0m";
        }

        return $markup;
    }

    /**
     * Traverses through mappingInformation array and aggregates by index and type names
     *
     * @param \Flowpack\ElasticSearch\Mapping\MappingCollection $mappingCollection
     * @throws \Flowpack\ElasticSearch\Exception
     * @return array with index names as keys, second level type names as keys
     */
    protected function buildArrayFromMappingCollection(\Flowpack\ElasticSearch\Mapping\MappingCollection $mappingCollection)
    {
        $return = array();

        /** @var $mappingInformation \Flowpack\ElasticSearch\Domain\Model\Mapping */
        foreach ($mappingCollection as $mappingInformation) {
            $indexName = $mappingInformation->getType()->getIndex()->getName();
            $typeName = $mappingInformation->getType()->getName();
            if (isset($return[$indexName][$typeName])) {
                throw new \Flowpack\ElasticSearch\Exception('There was more than one mapping present in index %s with type %s, which must not happen.', 1339758480);
            }

            $return[$indexName][$typeName]['mappingInstance'] = $mappingInformation;
            $return[$indexName][$typeName]['properties'] = $mappingInformation->getProperties();
        }

        return $return;
    }
}
