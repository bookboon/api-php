#Bookboon API PHP Class
[![Build Status](https://travis-ci.org/bookboon/api-php.svg?branch=master)](https://travis-ci.org/bookboon/api-php) [![Code Climate](https://codeclimate.com/github/bookboon/api-php/badges/gpa.svg)](https://codeclimate.com/github/bookboon/api-php) [![Test Coverage](https://codeclimate.com/github/bookboon/api-php/badges/coverage.svg)](https://codeclimate.com/github/bookboon/api-php/coverage)


The PHP class is a wrapper for the Bookboon.com API. Because this is just a wrapper class you should familiarize yourself with the [REST api](https://github.com/bookboon/api) before using it.

##Usage

To use the Bookboon AIP you are required to have an application id and secret (`$API_ID` and `$API_SECRET` below), see the [API page](https://github.com/bookboon/api) for details. Install via composer:

    composer require bookboon/api

To use it without composer, use with any PSR-0 compatible autoloader or require every file manually.  

The simplest way to get a book is to use the getBook method:
	
	$bookboon = new Bookboon($API_ID, $API_SECRET, array(/*optional named array to set request headers*/));
	$book = $bookboon->getBook("BOOK_GUID");

That will return a Book object with public getters for every property. There are plenty more simple get functions:  

	$category = $bookboon->getCategory("CATEGORY_GUID"); // return Category object
	$reviews = $bookboon->getReviews("BOOK_GUID"); // return array of Review
	$search = $bookboon->getSearch("query text"); // return array of Book
	$recommendations = $bookboon->getRecommendations(array("BOOK_ID_1", "BOOK_ID_2"); // return array of Book
	$questions = $bookboon->getQuestions(); // return array of Question

Finally you can download a book usually the following, you need to send a unique user identifier `handle` for every unique user (for instance a user id, email):  

	$url = $bookboon->getBookDownloadUrl("BOOK_GUID", array("handle" => "user@email"));
	// Send the $url in a redirect header to the user
	
> **Important:** Do **NOT** store this value as it will change constantly.

## Use api raw

You can also use the `api` method to get database from the API. To pass variables to the API send an array with the `api` function:
	
	/* The bacon-loving student */
	$vars = array('post' => array( 'answer[0]' => '6230e12c-68d8-45d5-8f02-1d3997713150',
				  			       'answer[1]' => '5aca0fe1-0d93-41b1-8691-aa242a526f17'
								 )
				 );
								
	$bookboon->api('/questions', $vars);

> **Note:** To make the php class more versatile you need to tell it whether to pass variables using POST or GET methods. The `api` function will only accept keys named 'post' and 'get' and parse their respective arrays into the correct query strings. 

##Result

Results from the `api` method is json decoded arrays of data directly from the API, if you use any of the other methods (`getbooks`, `getCategories` etc.) an appropiate object will be returned.

##Exceptions

The wrapper will throw a few different exceptions. If API responds with an unhandled HTTP status such as if a variabls are missing (403), the posted data is malformed (400) or an unknown API error (500). You may wish to catch these errors, like so:
	
	$bookboon = new Bookboon($API_ID, $API_SECRET);
	
	try {
		print_r($bookboon->api('/recommendations', array(
            'get' => array(
                'books' => $book_id
            )));
	} 
	catch (NotFoundException $e) {
	    // handle exception here
	}

Right now we throw the following exceptions:

`ApiSyntaxException` - Usually missing or malformed parameters  
`AuthenticationException` - Bad credentials  
`GeneralApiException` - When some unknown goes wrong, please report this to us  
`NotFoundException` - API returns not found status (404)  
 

##Cache

The wrapper class provides a cache interface to be used to speed up GET queries. At the moment only memcached is implemented. To set the cache provider use the `setCache` method:  

	$bookboon->setCache(new \Bookboon\Api\Memcached($server, $port, $timeToLive));

To implement your own provider cache software, make sure your interface imlements ` \Bookboon\Api\Cache`. It only has three methods: `save`, `get` and `delete`, so it should be easy enough to do.

