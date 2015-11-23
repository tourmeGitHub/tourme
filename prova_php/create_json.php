<?php
/**
 * Created by PhpStorm.
 * User: biagiomontesano
 * Date: 22/10/15
 * Time: 18:16
 */

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
        for($i = 1; $i<$num_elements-1; $i++)
        {
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

    echo json_encode($search_radii);
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




try
{
    $info = get_driving_information('via Tiburtina 538, Roma', 'via Ariosto 25, Roma');
    $steps = null;
    if(!$info)
        echo 'No info';

    else
         $steps = createJsonFromResponse($info);
    //$steps = json_decode($steps);
    //$steps = $steps_txt;
    //echo 'Num of steps: ' . count($steps[0]);
    selectLongestSteps($steps, 500);
    //echo geodeticDistance(41.91, 12.45, 45.48, 9.18);
    //echo distance(41.91, 12.45, 45.48, 9.18, "K");
}
catch(Exception $e)
{
    echo 'Caught exception: '.$e->getMessage()."\n";
}