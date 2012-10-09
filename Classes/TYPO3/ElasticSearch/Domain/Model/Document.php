<?php
namespace TYPO3\ElasticSearch\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3-package "TYPO3.ElasticSearch".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A Document which itself holds the data
 */
class Document {

	/**
	 * @var \TYPO3\ElasticSearch\Domain\Model\AbstractType
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
	 * @var integer
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
	 * @var boolean
	 */
	protected $dirty = TRUE;

	/**
	 * @param \TYPO3\ElasticSearch\Domain\Model\AbstractType $type
	 * @param array $data
	 * @param string $id
	 * @param null $version
	 */
	public function __construct(AbstractType $type, array $data = NULL, $id = NULL, $version = NULL) {
		$this->type = $type;
		$this->data = $data;
		$this->id = $id;
		$this->version = $version;
	}

	/**
	 * When cloning (locally), the cloned object doesn't represent a stored one anymore,
	 * so reset id, version and the dirty state.
	 */
	public function __clone() {
		$this->id = NULL;
		$this->version = NULL;
		$this->setDirty();
	}

	/**
	 * @param string $method
	 * @param string $path
	 * @param array $arguments
	 * @param string $content
	 *
	 * @return \TYPO3\ElasticSearch\Transfer\Response
	 */
	protected function request($method, $path = NULL, $arguments = array(), $content = NULL) {
		return $this->type->request($method, $path, $arguments, $content);
	}

	/**
	 * Stores this document. If ID is given, PUT will be used; else POST
	 *
	 * @throws \TYPO3\ElasticSearch\Exception
	 * @return void
	 */
	public function store() {
		if ($this->id !== NULL) {
			$method = 'PUT';
			$path = '/' . $this->id;
		} else {
			$method = 'POST';
			$path = '';
		}
		$response = $this->request($method, $path, array(), json_encode($this->data));
		$treatedContent = $response->getTreatedContent();

		if ($treatedContent['ok'] !== TRUE) {
			throw new \TYPO3\ElasticSearch\Exception('An error occured while trying to store a document.', 1339149673);
		}

		$this->id = $treatedContent['_id'];
		$this->version = $treatedContent['_version'];
		$this->dirty = FALSE;
	}

	/**
	 * @param boolean $dirty
	 */
	protected function setDirty($dirty = TRUE) {
		$this->dirty = $dirty;
	}

	/**
	 * @return boolean
	 */
	public function isDirty() {
		return $this->dirty;
	}

	/**
	 * @return integer
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData($data) {
		$this->data = $data;
		$this->setDirty();
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Gets a specific field's value from this' data
	 * @param string $fieldName
	 * @param boolean $silent
	 *
	 * @throws \TYPO3\ElasticSearch\Exception
	 * @return mixed
	 */
	public function getField($fieldName, $silent = FALSE) {
		if (!array_key_exists($fieldName, $this->data) && $silent === FALSE) {
			throw new \TYPO3\ElasticSearch\Exception(sprintf('The field %s was not present in data of document in %s/%s.', $fieldName, $this->type->getIndex()->getName(), $this->type->getName()), 1340274696);
		}
		return $this->data[$fieldName];
	}
}

?>