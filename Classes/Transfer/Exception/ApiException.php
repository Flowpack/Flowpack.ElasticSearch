<?php
namespace Flowpack\ElasticSearch\Transfer\Exception;

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
 * This exception type is intended to map any error output that was returned by ElasticSearch itself
 * If, for example, ElasticSearch returns {"error":"IndexMissingException[[foo_bar] missing]","status":404}
 * this exception is raised.
 */
class ApiException extends \Flowpack\ElasticSearch\Transfer\Exception
{
}
