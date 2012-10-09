<?php
namespace TYPO3\ElasticSearch\Domain\Factory;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Client factory
 * @FLOW3\Scope("singleton")
 */
class ClientFactory {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param string $bundle
	 *
	 * @throws \TYPO3\ElasticSearch\Exception
	 * @return \TYPO3\ElasticSearch\Domain\Model\Client
	 */
	public function create($bundle = NULL) {
		if ($bundle === NULL) $bundle = 'default';

		if (!isset($this->settings['clients'][$bundle]) || !is_array($this->settings['clients'][$bundle])) {
			throw new \TYPO3\ElasticSearch\Exception('The inquired client settings bundle "' . $bundle . '" is not present in setting "TYPO3.ElasticSearch.clients".', 1338890487);
		}
		$clientsSettings = $this->settings['clients'][$bundle];

		$clientConfigurations = $this->buildClientConfigurations($clientsSettings);

		$client = new \TYPO3\ElasticSearch\Domain\Model\Client();
		$client->setClientConfigurations($clientConfigurations);
		return $client;
	}

	/**
	 * @param $clientsSettings
	 *
	 * @return array
	 * @throws \TYPO3\ElasticSearch\Exception
	 */
	protected function buildClientConfigurations($clientsSettings) {
		$clientConfigurations = array();
		foreach ($clientsSettings AS $clientSettings) {
			$configuration = new \TYPO3\ElasticSearch\Domain\Model\Client\ClientConfiguration();
			foreach ($clientSettings AS $settingKey => $settingValue) {
				$setterMethodName = 'set' . ucfirst($settingKey);
				try {
					call_user_func(array($configuration, $setterMethodName), $settingValue);
				} catch (\TYPO3\FLOW3\Error\Exception $exception) {
					$exceptionMessage = 'Setting key "' . $settingKey . '" as client configuration value is not allowed. Refer to the Settings.yaml.example for the supported keys.';
					throw new \TYPO3\ElasticSearch\Exception($exceptionMessage, 1338886877, $exception);
				}
			}
			$clientConfigurations[] = $configuration;
		}
		return $clientConfigurations;
	}
}

?>