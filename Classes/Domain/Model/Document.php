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

use Flowpack\ElasticSearch\Exception as ElasticSearchException;
use Flowpack\ElasticSearch\Transfer\Response;

/**
 * A Document which itself holds the data
 */
class Document
{
    /**
     * @var AbstractType
     */
    protected $type;

    /**
     * The actual data to store to the document
     *
     * @var array
     */
    protected $data;

    /**
     * The version that has been assigned to this document.
     *
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    protected $id;

    /**
     * Whether this document represents the state like it should be at the storage.
     * With a fresh instance of this document, or a conducted change, this flag gets set to TRUE again.
     * When retrieved from the storage, or successfully set to the storage, it's FALSE.
     *
     * @var bool
     */
    protected $dirty = true;

    /**
     * @param AbstractType $type
     * @param array $data
     * @param string $id
     * @param int $version
     */
    public function __construct(AbstractType $type, ?array $data = null, ?string $id = null, ?int $version = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->id = $id;
        $this->version = $version;
    }

    /**
     * When cloning (locally), the cloned object doesn't represent a stored one anymore,
     * so reset id, version and the dirty state.
     *
     * @return void
     */
    public function __clone()
    {
        $this->id = null;
        $this->version = null;
        $this->setDirty();
    }

    /**
     * Stores this document. If ID is given, PUT will be used; else POST
     *
     * @return void
     * @throws \Neos\Flow\Http\Exception
     * @throws ElasticSearchException
     */
    public function store(): void
    {
        if ($this->id !== null) {
            $method = 'PUT';
            $path = '/_doc/' . $this->id;
        } else {
            $method = 'POST';
            $path = '/_doc/';
        }

        $response = $this->request($method, $path, [], json_encode($this->getData()));
        $treatedContent = $response->getTreatedContent();

        $this->id = $treatedContent['_id'];
        $this->version = $treatedContent['_version'];
        $this->dirty = false;
    }

    /**
     * @return boolean
     */
    public function isDirty(): bool
    {
        return $this->dirty;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * The contents of this document
     *
     * @return array
     */
    public function getData(): array
    {
        $this->data[Mapping::NEOS_TYPE_FIELD] = $this->type->getName();
        return $this->data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
        $this->setDirty();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Gets a specific field's value from this' data
     *
     * @param string $fieldName
     * @param boolean $silent
     * @return mixed
     * @throws ElasticSearchException
     */
    public function getField(string $fieldName, bool $silent = false)
    {
        if (!array_key_exists($fieldName, $this->data) && $silent === false) {
            throw new ElasticSearchException(sprintf('The field %s was not present in data of document in %s/%s.', $fieldName, $this->type->getIndex()->getName(), $this->type->getName()), 1340274696);
        }

        return $this->data[$fieldName];
    }

    /**
     * @return AbstractType the type of this Document
     */
    public function getType(): AbstractType
    {
        return $this->type;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $arguments
     * @param string $content
     * @return Response
     * @throws ElasticSearchException
     * @throws \Neos\Flow\Http\Exception
     */
    protected function request(string $method, ?string $path = null, array $arguments = [], ?string $content = null): Response
    {
        return $this->type->request($method, $path, $arguments, $content);
    }

    /**
     * @param boolean $dirty
     * @return void
     */
    protected function setDirty(bool $dirty = true): void
    {
        $this->dirty = $dirty;
    }

}
