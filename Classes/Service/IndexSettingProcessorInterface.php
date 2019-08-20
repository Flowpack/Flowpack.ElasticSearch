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

/**
 * Index setting processor
 */
interface IndexSettingProcessorInterface
{
    /**
     * @return int
     */
    public static function getPriority();

    /**
     * Check if the given setting can be processed
     *
     * @param array $settings
     * @param string $path
     * @return array the processed settings passed to the next processor
     */
    public function canProcess(array $settings, $path);

    /**
     * Process index settings
     *
     * @param array $settings
     * @param string $path
     * @param string $indexName
     * @return array the processed settings passed to the next processor
     */
    public function process(array $settings, $path, $indexName);
}
