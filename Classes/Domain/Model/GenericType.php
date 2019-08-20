<?php
declare(strict_types=1);

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

/**
 * An abstract document type. Implement your own or use the GenericType provided with this package.
 */
class GenericType extends AbstractType
{
    /**
     * @param Index $index
     * @param string $name
     */
    public function __construct(Index $index, $name)
    {
        parent::__construct($index, $name);
    }
}
