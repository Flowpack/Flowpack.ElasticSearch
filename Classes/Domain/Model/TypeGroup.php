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
 * A type group that is for requests against multiple types, like in localhost:9200/foo/onetype,anothertype/_search....
 */
class TypeGroup extends AbstractType
{
    /**
     * @var array<AbstractType>
     */
    protected $types = [];

    /**
     * @param Index $index
     * @param array $types
     */
    public function __construct(Index $index, array $types)
    {
        parent::__construct($index);
        $this->types = $types;

        $names = [];
        foreach ($this->types as $type) {
            $names[] = $type->getName();
        }
        $this->name = implode(',', $names);
    }
}
