===========
Transformer
===========

The Transformer classes allows you to transform your data before it's stored in elasticsearch.

Transformer annotations
=======================
This package ships several Transformer by default:

* CollectionStringCastTransformer:
  
  * Iterates over an Collection/Array and casts all items to a string and returns an array with strings

* DateTransformer

  * Converts a DateTime-Object to formatted string. Defaults to Y-m-d

* ObectIdentifierTransformer

  * Converts an object to it's persistence identifier

* StringCastTransformer

  * Converts a value to a string

* TextCastTransformer

  * Converts a value to a string


Usage
=====

To transform an objects property at index time you need to annotate your value like this:

*Example: Use a Transformer* ::

    /**
     * @var \DateTime
     * @ElasticSearch\Transform(options={"format" = "Y-m-d H:m:s"}, type="date")
     */
    protected $date;


The `type` option is usded to determine the corresponding transformer class. Date would resolve to `Flowpack\ElasticSearch\Indexer\Object\Transform\DateTransformer`.
and call it's implementation of `transformByAnnotation($source, TransformAnnotation $annotation)`.
All default transformer can be used just like this.


Implement a custom Transformer
==============================
If you need a custom transformer you need to implement the TrnasformerInterface. 
It declares two methods:

* getTargetMappingType: return value is used as mapping type in elasticsearch (one of https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html)

* transformByAnnotation: actual implementation of your transform process


*Example: Custom Transformer that implements TransformerInterface and crops the source length* ::
    
    <?php
    namespace Some\Vendor\Indexer\Object\Transform;

    use Flowpack\ElasticSearch\Annotations\Transform as TransformAnnotation;
    use Neos\Flow\Annotations as Flow;

    /**
    * @Flow\Scope("singleton")
    */
    class CropTransformer implements TransformerInterface
    {

        /**
        * Returns the Elasticsearch type that is used as mappping type
        * 
        * @return string
        */
        public function getTargetMappingType() {
            return 'text' 
        }

        /**
        * This is actually callled to transform the $source value
        *
        * @param mixed $source
        * @param TransformAnnotation $annotation
        * @return mixed
        */
        public function transformByAnnotation($source, TransformAnnotation $annotation) {

            if ($source === null) {
                return '';
            }

            if ($annotation->options['length']) {
                return substr((string) $source, 0, $annotation->options['length']);
            }
            
            return (string) $source
        }
    }



*Example: Annotation usage:* ::

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
         * @ElasticSearch\Transform(options={"length" = 20}, type="Some\Vendor\Indexer\Object\Transform\CropTransformer")
         */
        protected $message;

        /**
         * @var \DateTime
         */
        protected $date;
    }


With this configuration the message will always be cropped to 20 chars when it's indexed

