<?php
namespace TYPO3\ElasticSearch\Command;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\Flow\Annotations as Flow;

/**
 * Provides CLI features for mapping handling
 *
 * @Flow\Scope("singleton")
 */
class MappingCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\ElasticSearch\Mapping\EntityMappingBuilder
	 */
	protected $entityMappingBuilder;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\ElasticSearch\Mapping\BackendMappingBuilder
	 */
	protected $backendMappingBuilder;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\ElasticSearch\Domain\Factory\ClientFactory
	 */
	protected $clientFactory;

	/**
	 * Shows the status of the current mapping...
	 *
	 * @param string $clientName The client name for the configuration. Defaults to the default client configured.
	 */
	public function showStatusCommand($clientName = NULL) {
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
			$this->outputFormatted('index %s:', array($this->markupDiffValue(isset($entityMappingCollection[$indexName]) ? $indexName : NULL, isset($backendMappingCollection[$indexName]) ? $indexName : NULL)));
			foreach ($typeSet as $typeName => $mappingSet) {
				$propertiesSet = $mappingSet['properties'];
				$this->outputFormatted('type %s:', array($this->markupDiffValue(isset($entityMappingCollection[$indexName][$typeName]) ? $typeName : NULL, isset($backendMappingCollection[$indexName][$typeName]) ? $typeName : NULL)), 4);
				foreach ($propertiesSet as $propertyName => $properties) {
					$entityProperties = \TYPO3\Flow\Utility\Arrays::getValueByPath($entityMappingCollection, array($indexName, $typeName, 'properties', $propertyName));
					$backendProperties = \TYPO3\Flow\Utility\Arrays::getValueByPath($backendMappingCollection, array($indexName, $typeName, 'properties', $propertyName));

					$this->outputFormatted('property %s:', array($this->markupDiffValue($entityProperties ? $propertyName : NULL, $backendProperties ? $propertyName : NULL)), 8);
					foreach ($properties AS $key => $value) {
						$keyMarkup = $this->markupDiffValue(isset($entityProperties[$key]) ? $key : NULL, isset($backendProperties[$key]) ? $key : NULL);
						$valueMarkup = $this->markupDiffValue(isset($entityProperties[$key]) ? $entityProperties[$key] : NULL, isset($backendProperties[$key]) ? $backendProperties[$key] : NULL);
						$this->outputFormatted("%s : %s", array($keyMarkup, $valueMarkup), 12);
					}
				}
				$this->outputLine();
			}
			$this->outputLine();
		}

		if (count($indicesWithoutTypeInformation = $this->backendMappingBuilder->getIndicesWithoutTypeInformation())) {
			$this->outputFormatted("\x1b[43mNotice:\x1b[0m The following indices are present in the backend's mapping but having no type configuration, can hence be regarded as garbage:");
			foreach ($indicesWithoutTypeInformation AS $indexName) {
				$this->outputFormatted('* %s', array($indexName), 4);
			}
		}
	}

	/**
	 * This command will adjust the backend's mapping to the mapping the entity status prescribes.
	 *
	 * @param string $clientName The client name for the configuration. Defaults to the default client configured.
	 */
	public function convergeCommand($clientName = NULL) {
		$client = $this->clientFactory->create($clientName);

		$entityMappingCollection = $this->entityMappingBuilder->buildMappingInformation();
		$this->backendMappingBuilder->setClient($client);
		$backendMappingCollection = $this->backendMappingBuilder->buildMappingInformation();

		$additiveMappings = $entityMappingCollection->diffAgainstCollection($backendMappingCollection);
		/** @var $mapping \TYPO3\ElasticSearch\Domain\Model\Mapping */
		foreach ($additiveMappings AS $mapping) {
			$this->outputLine('Attempt to apply properties to %s/%s: %s... ', array(
				$mapping->getType()->getIndex()->getName(),
				$mapping->getType()->getName(),
				print_r($mapping->getProperties(), TRUE)
			));
			$mapping->getType()->getIndex()->setClient($client);
			$response = $mapping->apply();
			$treatedResponse = $response->getTreatedContent();
			if ($response->getStatusCode() === 200 && isset($treatedResponse['ok']) && $treatedResponse['ok'] === TRUE) {
				$this->outputLine('<b>OK</b>');
			} else {
				$this->outputFormatted('<b>NOT OK</b>, response code was %d, response body was: %s', array($response->getStatusCode(), $response->getOriginalResponse()->getContent()), 4);
			}
		}
		if (0 === $additiveMappings->count()) {
			$this->outputLine('No mappings were to be applied.');
		}
	}

	/**

	 */
	protected function printLegend() {
		$legendText = "
" . $this->markupDiffValue(NULL, 'something') . " defined in backend, but not in entities
" . $this->markupDiffValue('something', NULL) . " defined in entities, but not in backend
" . $this->markupDiffValue('something', 'something') . " defined both in entities and backend, all OK
" . $this->markupDiffValue('something', 'different') . " different in entities and backend
";
		$this->outputFormatted('<b>Legend:</b>');
		$this->outputFormatted($legendText, array(), 4);
	}

	/**
	 * @param $entityValue
	 * @param $backendValue
	 *
	 * @return string
	 */
	protected function markupDiffValue($entityValue, $backendValue) {
		$markup = '';
		if ($entityValue === NULL || $backendValue === NULL || $entityValue === $backendValue) {
			$markup .= "\x1b[" . ($entityValue ? '31' : '30') . ';' . ($backendValue ? '42' : '0') . 'm';
			$markup .= $entityValue ? : $backendValue;
			$markup .= "\x1b[0m";
		} else {
			$markup .= "\x1b[31m" . $entityValue . "\x1b[0m";
			$markup .= "\x1b[30;42m" . $backendValue . "\x1b[0m";
		}

		return $markup;
	}

	/**
	 * Traverses through mappingInformation array and aggregates by index and type names
	 *
	 * @param \TYPO3\ElasticSearch\Mapping\MappingCollection $mappingCollection
	 *
	 * @throws \TYPO3\ElasticSearch\Exception
	 * @return array with index names as keys, second level type names as keys
	 */
	protected function buildArrayFromMappingCollection(\TYPO3\ElasticSearch\Mapping\MappingCollection $mappingCollection) {
		$return = array();

		/** @var $mappingInformation \TYPO3\ElasticSearch\Domain\Model\Mapping */
		foreach ($mappingCollection as $mappingInformation) {
			$indexName = $mappingInformation->getType()->getIndex()->getName();
			$typeName = $mappingInformation->getType()->getName();
			if (isset($return[$indexName][$typeName])) {
				throw new \TYPO3\ElasticSearch\Exception('There was more than one mapping present in index %s with type %s, which must not happen.', 1339758480);
			}

			$return[$indexName][$typeName]['mappingInstance'] = $mappingInformation;
			$return[$indexName][$typeName]['properties'] = $mappingInformation->getProperties();
		}

		return $return;
	}
}

?>