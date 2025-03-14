====================================
Flowpack.ElasticSearch documentation
====================================

This package provides an API for using Elasticsearch with Flow. The intention is to provide a simple,
`fluent-interface`_-driven framework respecting the paradigm of Elasticsearch, made for working best
with the features Flow already provides.

Setting up the clients
======================

Usually you will only need one *client*. A *client* is one target the search communicates against, hence will consist
of many *nodes*. Perform the setting in the appropriate ``Settings.yaml``::

	Flowpack:
	  ElasticSearch:
	    clients:
	        # default bundle that will be used if no more specific bundle name was supplied.
	      default:
	        - host: localhost
	          port: 9200
	        - host: localhost
	          port: 9201

	        # reserved bundle name that's used when running the package's functional tests.
	      FunctionalTests:
	        - host: localhost
	          port: 9200

In this example setup, there's one *client* with two *nodes*, both at ``localhost``, one at port ``9200``, the other
at port ``9201``. The ``FunctionalTests`` client is a reserved one that acts for functional testing. If you like to
have a dedicated server (recommended) for running the Functional Tests, set it up here.

During runtime, you have to provide the client's name you want to connect to, if it is not `default`.

When access to the Elasticsearch instance is protected through HTTP Basic Auth, you can provide the necessary username
and password in your client settings::

	Flowpack:
	  ElasticSearch:
	    clients:
	        # default bundle that will be used if no more specific bundle name was supplied.
	      default:
	        - host: my.elasticsearch-service.com
	          port: 443
	          scheme: https
	          username: john
	          password: mysecretpassword

The following options are available to configure TLS connections. These correspond to the options provided by cURL::

    sslVerifyHost: true
    sslVerifyPeer: true

    # CA certificate to verify the peer with
    sslCaInfo: './root-ca.pem'

    # file containing the private SSL key
    sslKey: './client-key.pem'

    # file containing the PEM formatted certificate
    sslCert: './client.pem'

    # password needed for the private SSL key
    sslKeyPasswd: 'some-password'

Running the Functional Tests
============================

For running the Functional Tests, the API will connect to the server and host you've set up in the ``Settings.yaml``
directive ``Flowpack.ElasticSearch.clients.FunctionalTests`` (see above). The test scenario will try to create a temporary
test *index* named ``flow_elasticsearch_functionaltests`` where it will work the test data on. If this index already
exists, the test will stop with a notification in order not to destroy some real-life-data in the unlikely case it has
that name.

After the tests run through, this index will be dropped rigorously.

Fetching a Client instance to work on
=====================================

For whatever you want to achieve, you need a *client* representation to work on. This is done via the ``ClientFactory``,
inject it wherever you need::

	class SampleClass {

		/**
		 * @Flow\Inject
		 * @var \Flowpack\ElasticSearch\Client\ClientFactory
		 */
		protected $clientFactory;

		public function sampleMethod() {
			$client = $this->clientFactory->create();
		}

	}

This will create a fresh client instance connecting to the client set (i.e. *nodes* you've configured in the
``Settings.yaml``). In this case, the ``create()`` method carries no argument, meaning the ``default`` client will be
taken.

Handling documents
==================

While handling *documents* (the actual data that is to be indexed), besides the *client*, there's additionally an
*index* and a *type* involved. And *index*, if not yet present, will be created automatically. A *type* specifies,
as its name allows to guess, the type of a document, for example "Tweet" that represents a Twitter tweet, or "Actor"
that represents a movie actor.

While a document itself is very generic (it consists of data, its mother *index* and the *type*), the type is specific
and reflects some real existing Model. Therefore the API provides an AbstractType where you as the developer inherit
your specific, intended types from, for example::

	class TwitterType extends \Flowpack\ElasticSearch\Domain\Model\AbstractType {
	}

This class might even be empty like in this case, it just has to be there. Per default, the name of the type is
determined from the full namespace. If you want to change that, just override the ``getName()`` method which is provided
by the ``AbstractType`` class.

So for storing a Twitter document, follow this example::

	class SampleClass {

		/**
		 * @Flow\Inject
		 * @var \Flowpack\ElasticSearch\Client\ClientFactory
		 */
		protected $clientFactory;

		public function sampleMethod() {
			$client = $this->clientFactory->create();
			$tweetsIndex = $client->findIndex('tweets');
			$twitterType = new TwitterType($tweetsIndex);
			$document = new \Flowpack\ElasticSearch\Document($twitterType, array(
				'user' => 'John',
				'date' => '2012-06-12',
				'text' => 'This is an example document data'
			));
			$document->store();
		}

	}

This will make the document being stored by transforming the object chain to its corresponding REST service call.


.. _fluent-interface: http://martinfowler.com/bliki/FluentInterface.html