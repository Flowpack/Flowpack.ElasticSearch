=======
Indexer
=======

The indexer allows collecting data that has to be indexed.

Indexing annotations
====================

The package introduces a new annotation called "Indexable". Use this annotation to define what objects/entities you want
to have indexed. If you annotate a class, all supported properties [#suppProperties]_ will be mapped. If you annotated
single properties, only these will be indexed. However, you have to annotate the class in every case.

*Example: Class where every property will be indexed* ::

	/**
	 * @Flow\Entity
	 * @ElasticSearch\Indexable("twitter", typeName="tweet")
	 */
	class Tweet {

		/**
		 * @var string
		 */
		protected $username;

		/**
		 * @var string
		 */
		protected $message;

		/**
		 * @var \DateTime
		 */
		protected $date;
	}

*Example: Class where only the ``message`` property will be indexed* ::

	/**
	 * @Flow\Entity
	 * @ElasticSearch\Indexable("twitter", typeName="tweet")
	 */
	class Tweet {

		/**
		 * @var string
		 */
		protected $username;

		/**
		 * @var string
		 * @ElasticSearch\Indexable
		 */
		protected $message;

		/**
		 * @var \DateTime
		 */
		protected $date;
	}

Mapping annotations
====================

ElasticSearch allows the mapping configuration done via annotations. See the example how to define mapping annotations:

*Example: Annotations to set up mapping directives* ::

	/**
	 * @var string
	 * @ElasticSearch\Mapping(boost=2.0, term_vector="with_offsets")
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var \DateTime
	 * @ElasticSearch\Mapping(format="YYYY-MM-dd")
	 */
	protected $date;


Note that for mapping creation, the type will automatically be determined from the PHP type the property is of.

Value transformations
=====================

For some properties it'll be necessary to conduct specific conversions in order to meet the requirements of
ElasticSearch. Declare custom type converters via their appropriate annotation::

	/**
	 * @var \DateTime
	 * @ElasticSearch\Mapping(format="YYYY-MM-dd")
	 * @ElasticSearch\Transform("Date")
	 */
	protected $date;

This will call the (supplied with the package) Date transformer and hand the converted value over to the ElasticSearch
engine.

Setting up the indexes
======================

As soon as you have proper configuration for your entities, you can create your index, with the CLI utility::

	flow index:create --index-name twitter

If you need advanced settings you can define them in your ``Settings.yaml``::

	Flowpack:
	  ElasticSearch:
	  	indexes:
		  default:
			'twitter':
			  analysis:
				filter:
				  elision:
					type: 'elision'
					articles: [ 'l', 'm', 't', 'qu', 'n', 's', 'j', 'd' ]
			  analyzer:
				custom_french_analyzer:
				  tokenizer: 'letter'
				  filter: [ 'asciifolding', 'lowercase', 'french_stem', 'elision', 'stop' ]
				tag_analyzer:
				  tokenizer: 'keyword'
				  filter: [ 'asciifolding', 'lowercase' ]

If you use multiple client configurations, please change the ``default`` key just below the ``indexes``.

You can update the index configuration with the following CLI::

	flow index:updateSettings --index-name twitter

Please check the ElasticSearch configuration to know witch settings are updatable. For any other settings changes, you
need to delete your indexes::

	flow index:delete --index-name twitter

.. [#suppProperties] *supported properties* are all scalar types, unless value transformation is applied.
