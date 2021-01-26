Elasticsearch 7 Connector
=========================
<small>provided by [Ellinaut](https://github.com/Ellinaut) </small>

---

## What is this library for?

This library provides a reusable structure and implementation for common tasks related to elasticsearch development with
PHP. The goal is to have the same structure in every application and to avoid rewriting simple tasks like creation of
indices or storing documents every time you need it.

The library uses the official library [elasticsearch/elasticsearch](https://github.com/elastic/elasticsearch-php) and
adds some more structure and features.

## Requirements

This library requires you to use PHP in the version 7.2 or higher and an elasticsearch server with version 7.x.

## Installation

The simplest way to install this library to your application is composer:

```composer require ellinaut/elasticsearch-7-connector```

## How to use this library in your application

Core of this library, and the entry point for each call from your application, is the
class `Ellinaut\ElasticsearchConnector\ElasticsearchConnector`.

So you need an instance of this class, which requires instances of
`Ellinaut\Elasticsearch\Connection\ConnectionFactoryInterface`,
`Ellinaut\Elasticsearch\NameProvider\NameProviderInterface` and
`Ellinaut\Elasticsearch\Connection\ResponseHandlerInterface` (optional).

Here is an example for instance which connect to localhost on the default port and does not change index or pipeline
names between PHP and elasticsearch:

```php
    use Ellinaut\ElasticsearchConnector\Connection\DsnConnectionFactory;
    use Ellinaut\ElasticsearchConnector\NameProvider\RawNameProvider;
    use Ellinaut\ElasticsearchConnector\ElasticsearchConnector;

    $elasticsearch = new ElasticsearchConnector(
        new DsnConnectionFactory('http://127.0.0.1:9200'),
        new RawNameProvider(),
        new RawNameProvider()
    );
```

## How to manage connections

Connections are created with the help of a ConnectionFactory (an instance
of `Ellinaut\ElasticsearchConnector\Connection\ConnectionFactoryInterface`). The created connection is an instance of
`Elasticsearch\Client` which is used for all actions executed with the `ElasticsearchConnector`.

The simplest way is to use the `DsnConenctionFactory` provided by this library (see above) but if your configuration is
more complex you are also able to implement the `ConnectionFactoryInterface` by your self.

## How to manage indices

The `ElasticsearchConnector` provides some methods to manage indices. Each method will result in one or more calls to an
instance of `Ellinaut\ElasticsearchConnector\Index\IndexManagerInterface`. Each index requires an instance of this
interface which have to be provided and registered to the connector by your application.

To simplify your implementation, your can use the trait `Ellinaut\ElasticsearchConnector\Index\IndexManagerTrait`. This
trait requires to you to implement the method `getIndexDefinition`, which have to provide the elasticsearch index
configuration as array. The trait uses this method and provides all methods required by the `IndexManagerInterface`.

Here is an example how a custom `IndexManager` could look like:

```php
    namespace App\IndexManager;
    
    use Ellinaut\ElasticsearchConnector\Index\IndexManagerInterface;
    use Ellinaut\ElasticsearchConnector\Index\IndexManagerTrait;

    class CustomIndexManager implements IndexManagerInterface {
        use IndexManagerTrait;
        
        /**
         * @return array
         */
        protected function getIndexDefinition() : array{
            return [
                'mappings' => [
                    'properties' => [
                        'test' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
            ];
        }
    }
```

To use your custom index manager, you have to register it on the connector instance:

```php
    /** @var \Ellinaut\ElasticsearchConnector\ElasticsearchConnector $elasticsearch */
    $elasticsearch->addIndexManager('custom_index', new App\IndexManager\CustomIndexManager());
```

Then you can use this index through these connector method calls:

```php
    /** @var \Ellinaut\ElasticsearchConnector\ElasticsearchConnector $elasticsearch */
    
    // Creates the index. Will throw an exception if the index already exists.
    $elasticsearch->createIndex('custom_index');
    
    // Creates the index only if it does not exist.
    $elasticsearch->createIndexIfNotExist('custom_index');
    
    // Deletes the index if it exists, then create the index new.
    $elasticsearch->recreateIndex('custom_index');
    
    // Migrate all documents from the index to a (new) migration index,
    // then recreate the old index and moves all the documents back.
    $elasticsearch->updateIndex('custom_index');
    
    // Deletes the index if it exists.
    $elasticsearch->deleteIndex('custom_index');
```

### Index Naming

Up to now only the internal index name was used in the documentation. If your application uses more than one
environment, it might make sense to use different index names for each environment, especially when hosted on the same
elasticsearch server.

That can be reached by using an index name provider, which is an instance
of `Ellinaut\ElasticsearchConnector\NameProvider\NameProviderInterface`. This provider will "decorate" your internal
index names for all elasticsearch requests and can be used to get the original (internal) index name from the (external)
elasticsearch index name.

Build in providers are:

* `Ellinaut\ElasticsearchConnector\NameProvider\RawNameProvider`: For equal naming in PHP and elasticsearch
* `Ellinaut\ElasticsearchConnector\NameProvider\PrefixedNameProvider`: For a custom prefix on external index names
* `Ellinaut\ElasticsearchConnector\NameProvider\SuffixedNameProvider`: For a custom suffix on external index names
* `Ellinaut\ElasticsearchConnector\NameProvider\ChainedNameProvider`: To combine two or more providers

If you need a custom naming strategy, you can also implement the `NameProviderInterface` with your custom name provider.

## How to manage pipeline

TODO

### Pipeline Naming

As with the indices also pipeline names could be different between PHP and elasticsearch. The name providers are the
same as for indices.

## How to manage document

TODO

## How to search documents

TODO

## How to handle more complex scenarios

TODO

---
<small>Ellinaut is powered by [NXI GmbH & Co. KG](https://nxiglobal.com)
and [BVH Bootsvermietung Hamburg GmbH](https://www.bootszentrum-hamburg.de).</small>
