google geocode api
==================

This script has been adapted from a fork.
Now returns all address data and without CURL, instead uses file_get_contents to get Google API JSON.


*Format of Google api response follows*
	
	Array
	(
	    [long_name] => WS2 9QL
	    [short_name] => WS2 9QL
	    [types] => Array
	        (
	            [0] => postal_code
	        )
	
	)
	Array
	(
	    [long_name] => Wednesbury Road
	    [short_name] => Wednesbury Rd
	    [types] => Array
	        (
	            [0] => route
	        )
	
	)
	Array
	(
	    [long_name] => Walsall
	    [short_name] => Walsall
	    [types] => Array
	        (
	            [0] => locality
	            [1] => political
	        )
	
	)
	Array
	(
	    [long_name] => Walsall
	    [short_name] => Walsall
	    [types] => Array
	        (
	            [0] => postal_town
	        )
	
	)
	Array
	(
	    [long_name] => West Midlands
	    [short_name] => West Midlands
	    [types] => Array
	        (
	            [0] => administrative_area_level_2
	            [1] => political
	        )
	
	)
	Array
	(
	    [long_name] => United Kingdom
	    [short_name] => GB
	    [types] => Array
	        (
	            [0] => country
	            [1] => political
	        )
	
	)
