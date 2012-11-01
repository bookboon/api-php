#Bookboon API PHP Class

The PHP class is a wrapper for the Bookboon.com API. It can be used in either public or authenticated mode, where the latter requires a key. Because this is just a wrapper class you should familiarize yourself with the REST api \([[Public]] and [[Authenticated]]\) before using it.

##Usage

###Public usage

	require 'bookboon.php';
	
	$bookboon = new Bookboon();
	print_r($bookboon->api('/categories'));
	
###Authenticated usage

This requires an API key (`$APIKEY` below).

	require 'bookboon.php';
	
	$bookboon = new Bookboon($APIKEY, $handle);
	print_r($bookboon->api('/recommendations'));

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
