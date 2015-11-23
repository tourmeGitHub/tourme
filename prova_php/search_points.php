<?php

computeInterestPoints();

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K")
        return ($miles * 1.609344);
    else if ($unit == "N")
        return ($miles * 0.8684);
    else
        return $miles;

}


function selectLongestSteps($steps_txt, $min_meters)
{
    // decode JSON
    $steps_dec = json_decode($steps_txt,true);

    // number of elements
    $num_elements = count($steps_dec);

    // output
    $search_radii = array();

    if(!$steps_dec || $num_elements == 0)
        return $search_radii;

    // local variables
    $temp_lat = $steps_dec[0]['start_lat'];
    $temp_lng = $steps_dec[0]['start_lng'];
    $temp_radius = $steps_dec[0]['step_length'];

    if($num_elements == 1 && $temp_radius >= $min_meters)
    {
        $local_array = array
        (
            'start_lat' => $temp_lat,
            'start_lng' => $temp_lng,
            'end_lat' => $steps_dec[0]['end_lat'],
            'end_lng' => $steps_dec[0]['end_lng'],
            'radius' => $temp_radius
        );

        array_push($search_radii, $local_array);
    }

    else
    {
        for($i = 1; $i<$num_elements; $i++)
        {
            //  echo $i . "<br>";
            //echo $i ." " . $steps_dec[$i]['step_length'] + $temp_radius . " " . round(distance($temp_lat, $temp_lng, $steps_dec[$i]['end_lat'], $steps_dec[$i]['end_lng'], "K")*1000) ."<br";
            //echo $steps_dec[$i]['step_length'] ."<br>";
            //echo round(distance($temp_lat, $temp_lng, $steps_dec[$i]['end_lat'], $steps_dec[$i]['end_lng'], "K")*1000) ."<br";
            //echo $steps_dec[$i]['end_lng'] . "<br>";
            if($steps_dec[$i]['step_length'] + $temp_radius >= $min_meters)
            {
                $local_array = array
                (
                    'start_lat' => $temp_lat,
                    'start_lng' => $temp_lng,
                    'end_lat' => $steps_dec[$i]['end_lat'],
                    'end_lng' => $steps_dec[$i]['end_lng'],
                    'radius' => round(distance($temp_lat, $temp_lng, $steps_dec[$i]['end_lat'], $steps_dec[$i]['end_lng'], "K")*1000)
                );


                array_push($search_radii, $local_array);
                $temp_lat = $steps_dec[$i]['end_lat'];
                $temp_lng = $steps_dec[$i]['end_lng'];
                $temp_radius = 0;
            }

            else
                $temp_radius += $steps_dec[$i]['step_length'];
        }
    }

    //echo json_encode($search_radii);
    return $search_radii;
}

function createJsonFromResponse($response_json)
{
    // output json
    $output_json = array();

    // number of steps
    $num_steps = count($response_json['routes'][0]['legs'][0]['steps']);
    //echo $num_steps;

    // fill the json
    for($i = 0; $i<$num_steps; $i++)
    {
        $local_array = array
        (
            'start_lat' => $response_json['routes'][0]['legs'][0]['steps'][$i]['start_location']['lat'],
            'start_lng' => $response_json['routes'][0]['legs'][0]['steps'][$i]['start_location']['lng'],
            'end_lat' => $response_json['routes'][0]['legs'][0]['steps'][$i]['end_location']['lat'],
            'end_lng' => $response_json['routes'][0]['legs'][0]['steps'][$i]['end_location']['lng'],
            'step_length' => $response_json['routes'][0]['legs'][0]['steps'][$i]['distance']['value'],
            'instruction' => $response_json['routes'][0]['legs'][0]['steps'][$i]['html_instructions']
        );

        array_push($output_json, $local_array);
    }


    return json_encode($output_json);
}

function get_driving_information($start, $finish)
{
    if (strcmp($start, $finish) != 0) {
        $start = urlencode($start);
        $finish = urlencode($finish);

        $url = 'http://maps.googleapis.com/maps/api/directions/json?origin=' . $start . '&destination=' . $finish . '&sensor=false';

        // get the json response
        $resp_json = file_get_contents($url);

        // decode the json
        $resp = json_decode($resp_json, true);
        //echo json_encode($resp);
        return $resp;
    }

    else
        return null;

}

function midpoint ($lat1, $lng1, $lat2, $lng2)
{
    // convert to radians
    $lat1 = deg2rad($lat1);
    $lng1 = deg2rad($lng1);
    $lat2 = deg2rad($lat2);
    $lng2 = deg2rad($lng2);

    // longitude delta
    $dlng = $lng2 - $lng1;

    // yaw
    $Bx = cos($lat2) * cos($dlng);
    $By = cos($lat2) * sin($dlng);
    $lat3 = atan2( sin($lat1)+sin($lat2),
        sqrt((cos($lat1)+$Bx)*(cos($lat1)+$Bx) + $By*$By ));
    $lng3 = $lng1 + atan2($By, (cos($lat1) + $Bx));
    $pi = pi();

    $midpoint = array
    (
        'mid_lat' => ($lat3*180)/$pi,
        'mid_lon' => ($lng3*180)/$pi
    );

   /* $midpoint['mid_lat'] = ($lat3*180)/$pi;
    $midpoint['mid_lon'] = ($lng3*180)/$pi;*/
    return $midpoint;
    //return ($lat3*180)/$pi .' '. ($lng3*180)/$pi;
}


function find_interest_points($LongestSteps){
    $fields = array('idNode', 'Lon', 'Lat');
    $num_steps= count($LongestSteps);
    //echo "count " .count($LongestSteps) ."<br>";
    $interest_points = array();

    for ($i = 0; $i < $num_steps; $i++)
    {
        //echo "loop " .$i ."<br>";
        // select coordinates of points
        $start_lon =$LongestSteps [$i]['start_lng'];
        $start_lat =$LongestSteps [$i]['start_lat'];
        $end_lon =$LongestSteps [$i]['end_lng'];
        $end_lat =$LongestSteps [$i]['end_lat'];

        // compute middlepoint
        $midpoint=midpoint($start_lat,$start_lon, $end_lat,$end_lon);

        // get midpoint's coordinates
        $mid_lat=$midpoint['mid_lat'];
        $mid_lon=$midpoint['mid_lon'];

        // DB Connection
        $db = new mysqli('localhost', 'root', '', 'prova'); // use your credentials
        if ($db->connect_errno)
        {
            printf("Connect failed: %s\n", $db->connect_error);
            exit();
        }


        // prepare query
//        $sql =
//            "(SELECT idNode,Lon,Lat, (1000*6371 * acos (cos ( radians(".$mid_lat.") ) * cos( radians( Lat ) ) * cos( radians( Lon ) - radians(".$mid_lon.") ) + sin ( radians(".$mid_lat.") )
//                * sin( radians( Lat ) )
//            )) AS distance
//            FROM node
//            HAVING distance <" .$LongestSteps [$i]['radius']/2 ."
//            ORDER BY distance
//            LIMIT 0 , 20)";


//  $sql =
//                "(SELECT idNode,Lon,Lat, (1000*60*1.1515*1.609344*degrees(
//                acos(sin(radians(Lat))*sin(radians(" .$mid_lat. "))+
//                cos(radians(Lat))*cos(radians(" .$mid_lat. "))*cos(radians(Lon-" .$mid_lon."))
//                ))) AS distance
//                FROM node
//                HAVING distance <" .$LongestSteps [$i]['radius']/2 . "
//                ORDER BY distance
//                LIMIT 0 , 20)";

     $sql =
             "(SELECT idNode,Lon,Lat, (1000*60*1.1515*1.609344*degrees(
             acos(sin(radians(Lat))*sin(radians(" .$mid_lat. "))+
             cos(radians(Lat))*cos(radians(" .$mid_lat. "))*cos(radians(Lon-" .$mid_lon."))
             ))) AS distance
             FROM node
             ORDER BY distance
             LIMIT 0 , 20)";

        // execute query
        $result = mysqli_query($db, $sql);
        //$result = mysqli_query($db, $sql_select_all);

        if (!$result)
            echo "Error: " .  "<br>" . $db->error;

        //echo "Numero righe " .mysqli_num_rows($result) ."<br>";
        if (mysqli_num_rows($result) > 0)
        {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)){
                //echo "mid_lat " .$mid_lat ." mid_lng " . $mid_lon . "<br>";
                //echo "id: " . $row["idNode"]. " Lon: " . $row["Lon"]." Lat: " . $row["Lat"]. " Distance: ". $row["distance"] . " radius: " .$LongestSteps [$i]['radius']/2 . "<br>";
                //echo $LongestSteps [$i]['radius']/2 . " " . distance($mid_lat, $mid_lon, $row["Lat"], $row["Lon"],"K") . "<br>";
                $local_array = array
                (
                    "interest_point_lat" => $row["Lat"],
                    "interest_point_lng" => $row["Lon"]
                );

                array_push($interest_points, $local_array);
            }

            echo "<br><br>";
        }

        else
            echo "0 results" . "<br>";

    }

    echo json_encode($interest_points);
    return $interest_points;
}

function computeInterestPoints()
{
    try
    {
        $info = get_driving_information('via Tiburtina 538, Roma', 'Piazza Re Di Roma, Roma');
        $steps = null;
        if (!$info)
            echo 'No info';

        else
            $steps = createJsonFromResponse($info);

        $LongestSteps = selectLongestSteps($steps, 600);
        json_encode($LongestSteps);
        $interest_points =  find_interest_points($LongestSteps);

        return $interest_points;

    }
    catch (Exception $e)
    {
        echo 'Caught exception: ' . $e->getMessage() . "\n";
    }
}


?>