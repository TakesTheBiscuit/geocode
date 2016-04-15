<?php
//error_reporting(E_ALL);

//@THIS SHOULD BECOME A CLASS IN FUTURE 

//testing singles right now
print_r(lookup('DHL Express	Isle Of Man Business Park	Cooil	Isle Of Man	IM2 2SE', 'Staples', 'TF3 4AS'));
exit; 

//run the whole lot  
//$run = lookupAllCsv();
  
   
function lookupAllCsv(){  
	//Mac line ending fix	
	ini_set('auto_detect_line_endings',TRUE);
	$rowsCount = 0;
	$geoCodedAddresses = 0;
	if (($handle = fopen("final_batch.csv", "r")) !== FALSE)
	{
	    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE)
	    {
		    //as an example of the below results in:
		    // CompanyName, , OX16 5EG
	    	$name = $data[0];
	    	$postcode = $data[2];
	    	
	    	//so basic.
			if($name && $postcode):
		        // get address components, forced to UK, we only have a name and a postcode to work from in this example
		        $full_address = $name.', '.$postcode. ' UK';
		        $geocoded = lookup($full_address, $name, $postcode); 
		                  
				if(is_array($geocoded)):
					
			        // create a sql db query
			        /*$qry = "INSERT INTO `dhl_drop_locations` ( name, street, latitude, longitude ) ".
			        //							 "VALUES ('$name', '".$geocoded['street']."', '".$geocoded['latitude']."', '".$geocoded['longitude']."');"; 
					 
			        echo $qry."\n<br/>";
			        */
			        
			        
			        // or, output as CSV for batch import into database 
			        
			        
			        //you know nothing john snooo 
			        echo implode(',', $geocoded);
			        echo "<br>\n";
			         
			        
			        // check if mysql rows has increased?/was query succesful???
			        $geoCodedAddresses++;
			         
		        else:
		        
					$rtnArray = array(
						'name'				=> $name,
						'street'			=> ' ',
						'town'				=> ' ',
						'county'			=> ' ',
						'postcode' 			=> $postcode, /* for the current data project i just want the original postcode back */
						
						
						'latitude' 			=> ' ',
						'longitude' 		=> ' ',
						'location_type' 	=> ' '
					);
					
					echo implode(',', $rtnArray);
			        echo "<br>\n";

		        
		        endif;  
		    endif;
		    
	        $rowsCount++;
	         
	    }
	    fclose($handle);
	  
		echo '<h4>RESULTS</h4>';
		echo 'DROP OFFS PROCESSED '.$geoCodedAddresses;
		echo " VS ".$rowsCount;
		echo " CSV ROWS";
	  
	} else {
		die('Error loading CSV!');
	}
}


function lookup($addressString, $name, $inPostcode)
{
	
	$string = str_replace (" ", "+", urlencode($addressString));
	
	$details_uri = "https://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false&key=AIzaSyC9kpwbV2cKuZTuFNPkqeMx4FP8VaQnH6Q";
	$response = json_decode(file_get_contents($details_uri),true);
	
	//echo $details_uri;
	
	// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
	if ($response['status'] != 'OK'){
		return $response['status'];
	} else { 
	
		$geo_parts = $response['results'][0]['geometry'];
		$address_components = $response['results'][0]['address_components'];
			
		$postcode = '';
		$street = '';
		$town = '';
		$county = ''; 
		
		foreach( $address_components as $arrComponent ):
			//we know that all $arrComponent are really sub arrays, google is trustworthy
		
			$dataType = $arrComponent['types'][0];
			
			// @todo there has to be a better way to map this out
			//if( $dataType == "postal_code" ):
			//	$postcode = $arrComponent['long_name'];
			//	if(!$postcode || $postcode == ''):
			//		$postcode = $inPostcode; //just use the input postcode!
			//	endif;
			//endif;
			
			if( $dataType == "route"  || $dataType == "premise" ):
				$street = $arrComponent['long_name'];
				
			endif;
			
			if( $dataType == "postal_town" ):
				$town = $arrComponent['long_name'];
			endif; 
			
			if( $dataType == "administrative_area_level_2" ):
				$county = $arrComponent['long_name'];
			endif; 
			
			
		endforeach; 
		  
		//var_dump($street);
		//this fixes NULL street issues by reversing that geocode back onto GOOGLE
		if(!$street || $street == '' || $street == ' '):
			//we dont know the street so reverse geocode back to the closest possible street
			$latlngcsv = $geo_parts['location']['lat'] . ','.$geo_parts['location']['lng'];
			$revGeo_uri = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latlngcsv."&sensor=false&key=AIzaSyC9kpwbV2cKuZTuFNPkqeMx4FP8VaQnH6Q";
			$reversedResponse = json_decode(file_get_contents($revGeo_uri),true);
			//echo '..reverse geocode: '. $revGeo_uri;
			if ($reversedResponse['status'] == 'OK')
			{
				$streetComponents = $reversedResponse['results'][0]['address_components'];
 
				foreach( $streetComponents as $arrStreetComponent ):
				
					$dataTypeStreet = $arrStreetComponent['types'][0];
					if( $dataTypeStreet == "route" || $dataTypeStreet == "premise" ):
						$street = $arrStreetComponent['long_name'];
					endif;
					
				endforeach;
				
			} else {
				//give up, perhaps in future use another supplier to lookup e.g openmaps or someone to cross reference this address.
				$street = ' ';
			} 
		endif;
		 
		//pack it up ready for return
		$rtnArray = array(
			'name'				=> $name,
			'street'			=> $street,
			'town'				=> $town,
			'county'			=> $county,
			'postcode' 			=> $inPostcode, /* for the current data project i just want the original postcode back */
			
			
			'latitude' 			=> $geo_parts['location']['lat'],
			'longitude' 		=> $geo_parts['location']['lng'],
			'location_type' 	=> $geo_parts['location_type']
		);
		 
		return $rtnArray;
	}
} 
?>