<?php
namespace Flowpack\ElasticSearch\Domain\Factory;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * Client factory
 * @Flow\Scope("singleton")
 */
class ClientFactory
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $bundle
     * @param string $clientClassName
     * @throws \Flowpack\ElasticSearch\Exception
     * @return \Flowpack\ElasticSearch\Domain\Model\Client
     * @return void
     */
    public function create($bundle = null, $clientClassName = 'Flowpack\ElasticSearch\Domain\Model\Client')
    {
        if ($bundle === null) {
            $bundle = 'default';
        }

        if (!isset($this->settings['clients'][$bundle]) || !is_array($this->settings['clients'][$bundle])) {
            throw new \Flowpack\ElasticSearch\Exception('The inquired client settings bundle "' . $bundle . '" is not present in setting "Flowpack.ElasticSearch.clients".', 1338890487);
        }
        $clientsSettings = $this->settings['clients'][$bundle];

        $clientConfigurations = $this->buildClientConfigurations($clientsSettings);

        $client = new $clientClassName();
        $client->setClientConfigurations($clientConfigurations);
        $client->setBundle($bundle);

        return $client;
    }

    /**
     * @param array $clientsSettings
     * @return array
     * @throws \Flowpack\ElasticSearch\Exception
     */
    protected function buildClientConfigurations(array $clientsSettings)
    {
        $clientConfigurations = array();
        foreach ($clientsSettings as $clientSettings) {
            $configuration = new \Flowpack\ElasticSearch\Domain\Model\Client\ClientConfiguration();
            foreach ($clientSettings as $settingKey => $settingValue) {
                $setterMethodName = 'set' . ucfirst($settingKey);
                try {
                    call_user_func(array($configuration, $setterMethodName), $settingValue);
                } catch (\TYPO3\Flow\Error\Exception $exception) {
                    $exceptionMessage = 'Setting key "' . $settingKey . '" as client configuration value is not allowed. Refer to the Settings.yaml.example for the supported keys.';
                    throw new \Flowpack\ElasticSearch\Exception($exceptionMessage, 1338886877, $exception);
                }
            }
            $clientConfigurations[] = $configuration;
        }

        return $clientConfigurations;
    }
}
