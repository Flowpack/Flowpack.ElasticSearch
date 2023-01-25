<?php
namespace Flowpack\ElasticSearch\Tests\Functional\Domain;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Domain\Model\Client;
use Flowpack\ElasticSearch\Transfer\Response;
use Neos\Flow\Tests\FunctionalTestCase;
use Flowpack\ElasticSearch\Domain\Model\Index;

class IndexTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function indexWithoutPrefix()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('getBundle')
            ->willReturn('FunctionalTests');

        $clientMock->expects($this->exactly(2))->method('request')
            ->withConsecutive(
                [
                    'PUT',
                    '/index_without_prefix/',
                    [],
                    json_encode([
                        'settings' => [
                            'index' => [
                                'number_of_replicas' => 2,
                                'soft_deletes' => [
                                    'enabled' => true
                                ]
                            ]
                        ]
                    ], JSON_THROW_ON_ERROR)
                ],
                // updateSettings should correctly filter soft_deletes as it's not in the allow list
                [
                    'PUT',
                    '/index_without_prefix/_settings',
                    [],
                    json_encode([
                        'index' => [
                            'number_of_replicas' => 2
                        ]
                    ], JSON_THROW_ON_ERROR)
                ]
            )
            ->willReturn($this->createStub(Response::class));

        $testObject = new Index('index_without_prefix', $clientMock);
        $testObject->create();
        $testObject->updateSettings();

        static::assertSame('index_without_prefix', $testObject->getOriginalName());
        static::assertSame('index_without_prefix', $testObject->getName());
    }

    /**
     * @test
     */
    public function indexWithPrefix()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('getBundle')
            ->willReturn('FunctionalTests');

        $clientMock->expects($this->exactly(2))->method('request')
            ->withConsecutive(
                [
                    'PUT',
                    '/prefix-index_with_prefix/',
                    [],
                    json_encode([
                        'settings' => [
                            'index' => [
                                'number_of_replicas' => 1,
                                'soft_deletes' => [
                                    'enabled' => true
                                ]
                            ]
                        ]
                    ], JSON_THROW_ON_ERROR)
                ],
                // updateSettings should correctly filter soft_deletes as it's not in the allow list
                [
                    'PUT',
                    '/prefix-index_with_prefix/_settings',
                    [],
                    json_encode([
                        'index' => [
                            'number_of_replicas' => 1
                        ]
                    ], JSON_THROW_ON_ERROR)
                ]
            )
            ->willReturn($this->createStub(Response::class));

        $testObject = new Index('index_with_prefix', $clientMock);
        $testObject->create();
        $testObject->updateSettings();

        static::assertSame('index_with_prefix', $testObject->getOriginalName());
        static::assertSame('prefix-index_with_prefix', $testObject->getName());
    }
}
