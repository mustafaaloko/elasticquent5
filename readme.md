# Elasticquent - Laravel 5

_Elasticsearch for Eloquent Laravel 5 Models_

**Note:**: Aloko\Elasticquent is a built on top of [adamfairholm/Elasticquent](https://github.com/adamfairholm/Elasticquent) which was built for Laravel 4.x. As the author of [that package](https://github.com/adamfairholm/Elasticquent), didn't merge the pull requests for changes to be compatible with Laravel 5. I decided to publish this one.

Elasticquent makes working with [Elasticsearch](http://www.elasticsearch.org/) and [Eloquent](http://laravel.com/docs/eloquent) models easier by mapping them to Elasticsearch types. You can use the default settings or define how Elasticsearch should index and search your Eloquent models right in the model.

Elasticquent uses the [official Elasticsearch PHP API](https://github.com/elasticsearch/elasticsearch-php). To get started, you should have a basic knowledge of how Elasticsearch works (indexes, types, mappings, etc). This is meant for use with Elasticsearch 1.x.

## Contents

* [Overview](#overview)
    * [How Elasticquent Works](#how-elasticquent-works)
* [Setup](#setup)
    * [Step 1 - Installation](#step1)
    * [Step 2 - Adding the Service Provider](#step2)
    * [Step 3 - Publishing the Config File](#step3)
    * [Final Step - Adding it to models](#final)
* [Next Steps](#next-steps)
    * [Indexes and Mapping](#indexes-and-mapping)
    * [Setting a Custom Index Name](#setting-a-custom-index-name)
    * [Setting a Custom Type Name](#setting-a-custom-type-name)
* [Indexing Documents](#indexing-documents)
* [Searching](#searching)
    * [Search Collections](#search-collections)
    * [Search Collection Documents](#search-collection-documents)
    * [Using the Search Collection Outside Elasticq uent](#using-the-search-collection-outside-wlasticquent)
* [More Options](#more-options)
    * [Document Ids](#document-ids)
    * [Document Data](#document-data)
    * [Using Elasticquent With Custom Collections](#using-elasticquent-with-custom-collections)
* [Roadmap](#roadmap)

## Overview

Elasticquent allows you take an Eloquent model and easily index and search its contents in Elasticsearch.

    $books = Book::where('id', '<', 200)->get();
    $books->addToIndex();

When you search, instead of getting a plain array of search results, you instead get an Eloquent collection with some special Elasticsearch functionality.

    $books = Book::search('Moby Dick')->get();
    echo $books->totalHits();

Plus, you can still use all the Eloquent collection functionality:

    $books = $books->filter(function($book)
    {
        return $book->hasISBN();
    });

Check out the rest of the documentation for how to get started using Elasticsearch and Elasticquent!

### How Elasticquent Works

When using a database, Eloquent models are populated from data read from a database table. With Elasticquent, models are populated by data indexed in Elasticsearch. The whole idea behind using Elasticsearch for search is that its fast and light, so you model functionality will be dictated by what data has been indexed for your document.

## Setup

Before you start using Elasticquent, make sure you've installed [Elasticsearch](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/_installation.html).

### <a name="step1"></a>Step 1 - Installation

    composer require aloko/elasticquent

### <a name="step2"></a>Step 2 - Adding the Service Provider

Add the following line into your **config/app.php** file's `providers` array:

    Aloko\Elasticquent\ElasticquentServiceProvider::class

### <a name="step3"></a>Step 3 - Publishing the Config File

From your command line, enter this line:

    php artisan vendor:publish --provider="Aloko\Elasticquent\ElasticquentServiceProvider" --tag="config"

This file will contain different configurations for your Elasticsearch instance (e.g. hosts, log files, default index, etc), 
file will reside in your **config/elasticquent.php**. You can change it for your preferred ones.

### <a name="final"></a>Final Step - Adding it to models

```php
use Aloko\Elasticquent\ElasticquentTrait;

class Book extends Eloquent {

    use ElasticquentTrait;

}
```

Now your Eloquent model has some extra methods that make it easier to index your model's data using Elasticsearch.

## Next Steps

### Indexes and Mapping

While you can definitely build your indexes and mapping through the Elasticsearch API, you can also use some helper methods to build indexes and types right from your models.

If you want a simple way to create indexes, Elasticquent models have a function for that:

    Book::createIndex($shards = null, $replicas = null);

For mapping, you can set a `mappingProperties` property in your model and use some mapping functions from there:

```php
protected $mappingProperties = array(
   'title' => array(
        'type' => 'string',
        'analyzer' => 'standard'
    )
);
```

If you'd like to setup a model's type mapping based on your mapping properties, you can use:

    Book::putMapping($ignoreConflicts = true);

To delete a mapping:

    Book::deleteMapping();

To rebuild (delete and re-add, useful when you make important changes to your mapping) a mapping:

    Book::rebuildMapping();

You can also get the type mapping and check if it exists.

    Book::mappingExists();
    Book::getMapping();

### Setting a Custom Index Name

Elastiquent will use `default` as your index name, but you can set a custom index name by editing **config/elasticquent.php** config file::

```php
<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elastiquent will use for all
    | Elastiquent models.
    */

    'default_index' => 'my_custom_index_name',

);
```

### Setting a Custom Type Name

By default, Elasticquent will use the table name of your models as the type name for indexing. If you'd like to override it, you can with the `getTypeName` function. Add this function into your models (e.g. Book).

```php
function getTypeName()
{
    return 'my_books';
}
```

To check if the type for the Elasticquent model exists yet, use `typeExists`:

    $typeExists = Book::typeExists();

## Indexing Documents

To index all the entries in an Eloquent model, use `addAllToIndex`:

    Book::addAllToIndex();

You can also index a collection of models:

    $books = Book::where('id', '<', 200)->get();
    $books->addToIndex();

You can index individual entries as well:

    $book = Book::find($id);
    $book->addToIndex();

You can also reindex an entire model:

    Book::reindex();

## Searching

There are two ways to search in Elasticquent. The first is a simple term search that searches all fields.

    $books = Book::search('Moby Dick');

The second is a query based search for more complex searching needs:

    $books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));

Both methods will return a search collection.

Here's the list of available paramers:

- `query` - Your ElasticSearch Query
- `aggregations` - The Aggregations you wish to return. [See Aggregations for details](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations.html).
- `sourceFields` - Limits returned set to the selected fields only
- `limit` - Number of records to return
- `offset` - Sets the record offset (use for paging results)
- `sort` - Your sort query

### Search Collections

When you search on an Elasticquent model, you get a search collection with some special functions.

You can get total hits:

    $books->totalHits();

Access the shards array:

    $books->shards();

Access the max score:

    $books->maxScore();

Access the timed out boolean property:

    $books->timedOut();

And access the took property:

    $books->took();

And access search aggregations - [See Aggregations for details](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations.html):

    $books->getAggregations();

### Search Collection Documents

Items in a search result collection will have some extra data that comes from Elasticsearch. You can always check and see if a model is a document or not by using the `isDocument` function:

    $book->isDocument();

You can check the document score that Elasticsearch assigned to this document with:

    $book->documentScore();

### Using the Search Collection Outside of Elasticquent

If you're dealing with raw search data from outside of Elasticquent, you can use the Elasticquent search results collection to turn that data into a collection.

```php
$client = new \Elasticsearch\Client();

$params = array(
    'index' => 'default',
    'type'  => 'books'
);

$params['body']['query']['match']['title'] = 'Moby Dick';

$collection = new Aloko\Elasticquent\ElasticquentResultCollection($client->search($params), new Book);

```

## More Options

### Document IDs

Elasticquent will use whatever is set as the `primaryKey` for your Eloquent models as the id for your Elasticsearch documents.

### Document Data

By default, Elasticquent will use the entire attribute array for your Elasticsearch documents. However, if you want to customize how your search documents are structured, you can set a `getIndexDocumentData` function that returns you own custom document array.

```php
function getIndexDocumentData()
{
    return array(
        'id'      => $this->id,
        'title'   => $this->title,
        'custom'  => 'variable'
    );
}
```
Be careful with this, as Elasticquent reads the document source into the Eloquent model attributes when creating a search result collection, so make sure you are indexing enough data for your the model functionality you want to use.

### Using Elasticquent With Custom Collections

If you are using a custom collection with your Eloquent models, you just need to add the `ElasticquentCollectionTrait` to your collection so you can use `addToIndex`.

```php
use Aloko\Elasticquent;

class MyCollection extends \Illuminate\Database\Eloquent\Collection {

    use ElasticquentCollectionTrait;
}
```
