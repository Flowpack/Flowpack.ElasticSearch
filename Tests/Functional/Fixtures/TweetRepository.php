<?php
namespace TYPO3\ElasticSearch\Tests\Functional\Fixtures;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.ElasticSearch".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("singleton")
 */
class TweetRepository  extends \TYPO3\FLOW3\Persistence\Repository {

}

?>