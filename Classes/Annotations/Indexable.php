<?php
namespace Flowpack\ElasticSearch\Annotations;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;

/**
 * @Annotation
 * @DoctrineAnnotation\Target({"CLASS", "PROPERTY"})
 */
final class Indexable
{
    /**
     * The name of the index this object has to be stored to, if target is CLASS
     *
     * @var string
     */
    public $indexName;

    /**
     * The type this object has to be stored as, if target is CLASS
     *
     * @var string
     */
    public $typeName;
}
