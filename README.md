#Bookboon API PHP Class
[![Build Status](https://travis-ci.org/bookboon/api-php.svg?branch=master)](https://travis-ci.org/bookboon/api-php) [![Code Climate](https://codeclimate.com/github/bookboon/api-php/badges/gpa.svg)](https://codeclimate.com/github/bookboon/api-php) [![Test Coverage](https://codeclimate.com/github/bookboon/api-php/badges/coverage.svg)](https://codeclimate.com/github/bookboon/api-php/coverage)


The PHP class is a wrapper for the Bookboon.com API. Because this is just a wrapper class you should familiarize yourself with the [REST api](https://github.com/bookboon/api) before using it.

##Usage

This requires a bookboon application id and secret (`$API_ID` and `$API_SECRET` below).

	require 'bookboon.php';
	
	$bookboon = new Bookboon($API_ID, $API_SECRET, array(/*optional named array to set request headers*/));
	print_r($bookboon->api('/categories'));

##Variables

To pass variables to the API send an array with the `api` function:
	
	/* The bacon-loving student */
	$vars = array('post' => array( 'answer[0]' => '6230e12c-68d8-45d5-8f02-1d3997713150',
				  			       'answer[1]' => '5aca0fe1-0d93-41b1-8691-aa242a526f17'
								 )
				 );
								
	$bookboon->api('/questions', $vars);

> **Note:** To make the php class more versatile you need to tell it whether to pass variables using POST or GET methods. The `api` function will only accept keys named 'post' and 'get' and parse their respective arrays into the correct query strings. 

##Result

The results is an array containing the decoded JSON response or if the call failed `false` value.

##Exceptions

The wrapper will throw an exception if API responds with an unhandled HTTP status such as if a variabls are missing (403), the posted data is malformed (400) or an unknown API error (500). You may wish to catch these errors, like so:

	require 'bookboon.php';
	
	$bookboon = new Bookboon($API_ID, $API_SECRET);
	
	try {
		print_r($bookboon->api('/recommendations', array(
            'get' => array(
                'books' => $book_id
            )));
	} 
	catch (Exception $e) {
	    // handle exception here
	}

##Cache

The wrapper class provides a cache interface to be used to speed up GET queries. At the moment only memcached is implemented and enabled by default - it is fairly trivial to implement another provider. To change caching provider alter the `$cache_class_name` class variable to the name of the class (and filename) or set to an empty string to disable. 
