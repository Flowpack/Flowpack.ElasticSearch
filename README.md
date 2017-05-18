# Flow Framework ElasticSearch Integration

This project connects the Flow Framework to Elasticsearch; enabling two main functionalities:

* Full-Text Indexing of Doctrine Entities
* Index configuration based on Annotations

## Elastic version support

You can set your Elasticsearch version by editing ```Settings.yaml```
(```Flowpack.ElasticSearch.version```) with the following value:

* ```1.x``` to support Elastic 1.2 to 1.7
* ```2.x``` to support Elastic 2.x


## Related package:

* [Flowpack.ElasticSearch.ContentRepositoryAdaptor](https://github.com/Flowpack/Flowpack.ElasticSearch.ContentRepositoryAdaptor): An adapter to support the Neos Content Repository
indexing and searching

## More documentation:

* [General Documentation](Documentation/Index.rst)
* [How to index your own model ?](Documentation/Indexer.rst)
