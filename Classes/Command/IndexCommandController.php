<?php
namespace Flowpack\ElasticSearch\Command;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Annotations\Indexable;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory;
use Flowpack\ElasticSearch\Domain\Model\Client;
use Flowpack\ElasticSearch\Domain\Model\Index;
use Flowpack\ElasticSearch\Indexer\Object\IndexInformer;
use Flowpack\ElasticSearch\Indexer\Object\ObjectIndexer;
use Neos\Error\Messages\Error;
use Neos\Error\Messages\Result as ErrorResult;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Exception;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * Provides CLI features for index handling
 *
 * @Flow\Scope("singleton")
 */
class IndexCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @Flow\Inject
     * @var IndexInformer
     */
    protected $indexInformer;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ObjectIndexer
     */
    protected $objectIndexer;

    /**
     * Create a new index in ElasticSearch
     *
     * @param string $indexName The name of the new index
     * @param string $clientName The client name to use
     *
     * @return void
     */
    public function createCommand($indexName, $clientName = null)
    {
        if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
            $this->outputFormatted("The index <b>%s</b> is not configured in the current application", [$indexName]);
            $this->quit(1);
        }

        $client = $this->clientFactory->create($clientName);
        try {
            $index = new Index($indexName, $client);
            if ($index->exists()) {
                $this->outputFormatted("The index <b>%s</b> exists", [$indexName]);
                $this->quit(1);
            }
            $index->create();
            $this->outputFormatted("Index <b>%s</b> created with success", [$indexName]);
        } catch (Exception $exception) {
            $this->outputFormatted("Unable to create an index named: <b>%s</b>", [$indexName]);
            $this->quit(1);
        }
    }

    /**
     * Update index settings
     *
     * @param string $indexName The name of the new index
     * @param string $clientName The client name to use
     *
     * @return void
     */
    public function updateSettingsCommand($indexName, $clientName = null)
    {
        if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
            $this->outputFormatted("The index <b>%s</b> is not configured in the current application", [$indexName]);
            $this->quit(1);
        }

        $client = $this->clientFactory->create($clientName);
        try {
            $index = new Index($indexName, $client);
            if (!$index->exists()) {
                $this->outputFormatted("The index <b>%s</b> does not exists", [$indexName]);
                $this->quit(1);
            }
            $index->updateSettings();
            $this->outputFormatted("Index settings <b>%s</b> updated with success", [$indexName]);
        } catch (Exception $exception) {
            $this->outputFormatted("Unable to update settings for <b>%s</b> index", [$indexName]);
            $this->quit(1);
        }
    }

    /**
     * Delete an index in ElasticSearch
     *
     * @param string $indexName The name of the index to be removed
     * @param string $clientName The client name to use
     *
     * @return void
     */
    public function deleteCommand($indexName, $clientName = null)
    {
        if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
            $this->outputFormatted("The index <b>%s</b> is not configured in the current application", [$indexName]);
            $this->quit(1);
        }

        $client = $this->clientFactory->create($clientName);
        try {
            $index = new Index($indexName, $client);
            if (!$index->exists()) {
                $this->outputFormatted("The index <b>%s</b> does not exists", [$indexName]);
                $this->quit(1);
            }
            $index->delete();
            $this->outputFormatted("Index <b>%s</b> deleted with success", [$indexName]);
        } catch (Exception $exception) {
            $this->outputFormatted("Unable to delete an index named: <b>%s</b>", [$indexName]);
            $this->quit(1);
        }
    }

    /**
     * Refresh an index in ElasticSearch
     *
     * @param string $indexName The name of the index to be removed
     * @param string $clientName The client name to use
     *
     * @return void
     */
    public function refreshCommand($indexName, $clientName = null)
    {
        if (!in_array($indexName, $this->indexInformer->getAllIndexNames())) {
            $this->outputFormatted("The index <b>%s</b> is not configured in the current application", [$indexName]);
        }

        $client = $this->clientFactory->create($clientName);
        try {
            $index = new Index($indexName, $client);
            if (!$index->exists()) {
                $this->outputFormatted("The index <b>%s</b> does not exists", [$indexName]);
                $this->quit(1);
            }
            $index->refresh();
            $this->outputFormatted("Index <b>%s</b> refreshed with success", [$indexName]);
        } catch (Exception $exception) {
            $this->outputFormatted("Unable to refresh an index named: <b>%s</b>", [$indexName]);
            $this->quit(1);
        }
    }

    /**
     * List available document type
     *
     * @return void
     */
    public function showConfiguredTypesCommand()
    {
        $classesAndAnnotations = $this->indexInformer->getClassesAndAnnotations();
        $this->outputFormatted("<b>Available document type</b>");
        /** @var $annotation Indexable */
        foreach ($classesAndAnnotations as $className => $annotation) {
            $this->outputFormatted("%s", [$className], 4);
        }
    }

    /**
     * Shows the status of the current mapping
     *
     * @param string $object Class name of a domain object. If given, will only work on this single object
     * @param boolean $conductUpdate Set to TRUE to conduct the required corrections
     * @param string $clientName The client name to use
     *
     * @return void
     */
    public function statusCommand($object = null, $conductUpdate = false, $clientName = null)
    {
        $result = new ErrorResult();

        $client = $this->clientFactory->create($clientName);

        $classesAndAnnotations = $this->indexInformer->getClassesAndAnnotations();
        if ($object !== null) {
            if (!isset($classesAndAnnotations[$object])) {
                $this->outputFormatted("Error: Object '<b>%s</b>' is not configured correctly, check the Indexable annotation.", [$object]);
                $this->quit(1);
            }
            $classesAndAnnotations = [$object => $classesAndAnnotations[$object]];
        }
        array_walk($classesAndAnnotations, function (Indexable $annotation, $className) use ($result, $client, $conductUpdate) {
            $this->outputFormatted("Object \x1b[33m%s\x1b[0m", [$className], 4);
            $this->outputFormatted("Index <b>%s</b> Type <b>%s</b>", [
                $annotation->indexName,
                $annotation->typeName,
            ], 8);
            $count = $client->findIndex($annotation->indexName)->findType($annotation->typeName)->count();
            if ($count === null) {
                $result->forProperty($className)->addError(new Error('ElasticSearch was unable to retrieve a count for the type "%s" at index "%s". Probably these don\' exist.', 1340289921, [
                    $annotation->typeName,
                    $annotation->indexName,
                ]));
            }
            $this->outputFormatted("Documents in Search: <b>%s</b>", [$count !== null ? $count : "\x1b[41mError\x1b[0m"], 8);

            try {
                $count = $this->persistenceManager->createQueryForType($className)->count();
            } catch (\Exception $exception) {
                $count = null;
                $result->forProperty($className)->addError(new Error('The persistence backend was unable to retrieve a count for the type "%s". The exception message was "%s".', 1340290088, [
                    $className,
                    $exception->getMessage(),
                ]));
            }
            $this->outputFormatted("Documents in Persistence: <b>%s</b>", [$count !== null ? $count : "\x1b[41mError\x1b[0m"], 8);
            if (!$result->forProperty($className)->hasErrors()) {
                $states = $this->getModificationsNeededStatesAndIdentifiers($client, $className);
                if ($conductUpdate) {
                    $inserted = 0;
                    $updated = 0;
                    foreach ($states[ObjectIndexer::ACTION_TYPE_CREATE] as $identifier) {
                        try {
                            $this->objectIndexer->indexObject($this->persistenceManager->getObjectByIdentifier($identifier, $className));
                            $inserted++;
                        } catch (\Exception $exception) {
                            $result->forProperty($className)->addError(new Error('An error occurred while trying to add an object to the ElasticSearch backend. The exception message was "%s".', 1340356330, [$exception->getMessage()]));
                        }
                    }
                    foreach ($states[ObjectIndexer::ACTION_TYPE_UPDATE] as $identifier) {
                        try {
                            $this->objectIndexer->indexObject($this->persistenceManager->getObjectByIdentifier($identifier, $className));
                            $updated++;
                        } catch (\Exception $exception) {
                            $result->forProperty($className)->addError(new Error('An error occurred while trying to update an object to the ElasticSearch backend. The exception message was "%s".', 1340358590, [$exception->getMessage()]));
                        }
                    }
                    $this->outputFormatted("Objects inserted: <b>%s</b>", [$inserted], 8);
                    $this->outputFormatted("Objects updated: <b>%s</b>", [$updated], 8);
                } else {
                    $this->outputFormatted("Modifications needed: <b>create</b> %d, <b>update</b> %d", [
                        count($states[ObjectIndexer::ACTION_TYPE_CREATE]),
                        count($states[ObjectIndexer::ACTION_TYPE_UPDATE]),
                    ], 8);
                }
            }
        });

        if ($result->hasErrors()) {
            $this->outputLine();
            $this->outputLine('The following errors occurred:');
            /** @var $error Error */
            foreach ($result->getFlattenedErrors() as $className => $errors) {
                foreach ($errors as $error) {
                    $this->outputLine();
                    $this->outputFormatted("<b>\x1b[41mError\x1b[0m</b> for \x1b[33m%s\x1b[0m:", [$className], 8);
                    $this->outputFormatted((string)$error, [], 4);
                }
            }
        }
    }

    /**
     * @param Client $client
     * @param string $className
     *
     * @return array
     */
    protected function getModificationsNeededStatesAndIdentifiers(Client $client, $className)
    {
        $query = $this->persistenceManager->createQueryForType($className);
        $states = [
            ObjectIndexer::ACTION_TYPE_CREATE => [],
            ObjectIndexer::ACTION_TYPE_UPDATE => [],
            ObjectIndexer::ACTION_TYPE_DELETE => [],
        ];
        foreach ($query->execute() as $object) {
            $state = $this->objectIndexer->objectIndexActionRequired($object, $client);
            $states[$state][] = $this->persistenceManager->getIdentifierByObject($object);
        }

        return $states;
    }
}
