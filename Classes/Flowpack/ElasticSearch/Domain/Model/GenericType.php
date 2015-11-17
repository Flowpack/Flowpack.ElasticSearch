<?php
namespace Flowpack\ElasticSearch\Domain\Model;

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
 * An abstract document type. Implement your own or use the GenericType provided with this package.
 */
class GenericType extends AbstractType
{
    /**
     * @param \Flowpack\ElasticSearch\Domain\Model\Index $index
     * @param string $name
     */
    public function __construct(Index $index, $name)
    {
        parent::__construct($index, $name);
    }
}
