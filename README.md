# solr_interface

PHP Object that interacts with Solr. Used to query for products in an E-commerce platform that sells mattresses.

This code is part of a web application written in [symfony 4](https://symfony.com/4) and uses [solarium](https://solarium.readthedocs.io/en/stable/), a Solr client library.

The purpose of this repository is to serve as a showcase of code written by [vascocajada](https://www.github.com/vascocajada).

## Installation

Clone the repository into your project and register the two objects as Services of your Symfony application.

```bash
git clone https://github.com/vascocajada/solr_interface.git
```

## Usage

Use [Symfony 4 Dependency Injection](https://symfony.com/doc/current/service_container/injection_types.html) to inject your Solr Service where needed.

```php
<?php


namespace App;

use \App\Service\Solr;

class ProductsSolr
{
    public function fetch(Solr $solr)
    {
        // use Solr Service here
    }
}
?>

```



### API

```php
<?php

    /** Create Solr Base Query **
     * instantiates solarium client with project credentials
     * creates a select query
     * loads filter structure
     * MUST receive a category id to filter results by default
     */
    $category_id = 1;
    $solr->createBaseQuery($category_id); 

    /** Add Filter **
     * add condition to solr query
     * $key - the name of the parameter - string
     * $value - value used to filter results. Can be multiple values separated by a semicolon - string
     */
    $key = 'brand';
    $value = 'ViscoElastic;Molaflex';
    $solr->addFilter($value, $key);

    /** Set Query **
     * add filter by free text to solr query
     * $query_string - the text to search - string
    */
    $query_string = 'comfortable mattress';
    $solr->setQuery($query_string);

    /** Add Range Filter **
     * add range condition to solr query
     * $key - the name of the parameter - string
     * $value - value used to filter results. Should contain two elements, the low threshold and high threshold, in that order - array
     */
    $key = 'translated_price_min';
    $value = ['20', '60'];
    $solr->addFilter($value, $key);

    /** Set Limit **
     * define how many results the query should return
     * $limit - number of results to return - int
     */
    $limit = 20;
    $solr->setLimit($limit);

    /** Set Page **
     * set page / offset of solr query
     * $page - offset of results - int
     */
    $page = 1;
    $solr->setPage($page);

    /** Set Sort **
     * set order of results
     * $sort - the name of the parameter to sort by and the order separated by a colon - string
     */
    $sort = 'brand:asc';
    $solr->setSort($sort);

    /** Set Facet Min Count **
     * define the number of minimum number of hits a filter option has to have for Solr to return its facet / count
     * $facet_min_count - the number of minimum hits - int
     */
    $facet_min_count = 1;
    $solr->setFacetMinCount($facet_min_count);

    /** Execute **
     * execute solr query
     * the results will be stored in the property $articles
     * the facets / filters will be stored in the property $filters
     */
    $solr->execute();
    $products = $solr->articles;
    $filters = $solr->filters;
?>
```
