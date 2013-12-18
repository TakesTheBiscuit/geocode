<?php
die();
if (($handle = fopen("customers.csv", "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE)
    {
        die(var_dump($data));
    	$country = $data[0];
    	$name = $data[1];
    	$address = $data[2];
    	$city = $data[3];
        $state = $data[4];
    	$zip = $data[5];
    	$telephone = $data[6];
    	$url = $data[7];
        
        // get lat/long
        $full_address = $address.' '.$city.', '.$state.' '.$zip.' '.$country;
        $geocode = lookup($full_address);
        $latitude = $geocode['latitude'];
        $longitude = $geocode['longitude'];

    	// mysql escape
    	$name = addslashes($name);
    	$address = addslashes($address);
    	$city = addslashes($city);
    	$region = "'".addslashes($state)."'";

        // create db query
        $qry = "INSERT INTO `iwd_storelocator` (title,is_active,phone,country_id,region_id,region,street,city,postal_code,stores,latitude,longitude,website) ".
        							 "VALUES ('$name',1,'$telephone','$country',NULL,$region,'$address','$city','$zip',0,'$latitude','$longitude','$url');";

        echo $qry."\n";
    }
    fclose($handle);
}
else
	die('Error loading CSV');

function lookup($string)
{
	$string = str_replace (" ", "+", urlencode($string));
	$details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $details_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = json_decode(curl_exec($ch), true);

	// If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
	if ($response['status'] != 'OK')
		return null;

	//print_r($response);
	$geometry = $response['results'][0]['geometry'];

	$array = array(
		'latitude' => $geometry['location']['lat'],
		'longitude' => $geometry['location']['lng'],
		'location_type' => $geometry['location_type'],
	);

	return $array;
}
