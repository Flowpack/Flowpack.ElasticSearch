<?php
declare(strict_types=1);

namespace Flowpack\ElasticSearch\Service;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Utility\PositionalArraySorter;

/**
 * Transform indices settings dynamically
 *
 * @Flow\Scope("singleton")
 */
final class DynamicIndexSettingService
{
    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param array $settings
     * @param string $path
     * @param string $indexName
     * @return array
     */
    public function process(array $settings, $path, $indexName)
    {
        foreach (static::getAllProcessors($this->objectManager) as $configuration) {
            /** @var IndexSettingProcessorInterface $processor */
            $processor = $this->objectManager->get($configuration['className']);
            if ($processor->canProcess($settings, $path)) {
                $settings = $processor->process($settings, $path, $indexName);
            }
        }

        return $settings;
    }

    /**
     * Returns all class names implementing the IndexSettingProcessorInterface.
     *
     * @Flow\CompileStatic
     * @param ObjectManagerInterface $objectManager
     * @return array
     */
    public static function getAllProcessors($objectManager)
    {
        /** @var ReflectionService $reflectionService */
        $reflectionService = $objectManager->get(ReflectionService::class);
        $processorClassNames = $reflectionService->getAllImplementationClassNamesForInterface(IndexSettingProcessorInterface::class);

        $processors = [];
        foreach ($processorClassNames as $processorClassName) {
            $processors[$processorClassName] = [
                'priority' => $processorClassName::getPriority(),
                'className' => $processorClassName
            ];
        }

        return array_reverse(
            (new PositionalArraySorter($processors, 'priority'))->toArray()
        );
    }
}
