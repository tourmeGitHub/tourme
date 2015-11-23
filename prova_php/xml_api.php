<?php
/**
 * Created by PhpStorm.
 * User: biagiomontesano
 * Date: 14/10/15
 * Time: 19:31
 */

function get_driving_information($start, $finish, $raw = false)
{
    if(strcmp($start, $finish) == 0)
    {
        $time = 0;
        if($raw)
        {
            $time .= ' seconds';
        }

        return array('distance' => 0, 'time' => $time);
    }

    $start  = urlencode($start);
    $finish = urlencode($finish);

    $distance   = 'unknown';
    $time		= 'unknown';

    $url = 'http://maps.googleapis.com/maps/api/directions/xml?origin='.$start.'&destination='.$finish.'&sensor=false';
    if($data = file_get_contents($url))
    {
        $xml = new SimpleXMLElement($data);

        if(isset($xml->route->leg->duration->value) AND (int)$xml->route->leg->duration->value > 0)
        {
            if($raw)
            {
                $distance = (string)$xml->route->leg->distance->text;
                $time	  = (string)$xml->route->leg->duration->text;
            }
            else
            {
                $distance = (int)$xml->route->leg->distance->value / 1000 / 1.609344;
                $time	  = (int)$xml->route->leg->duration->value;
            }
        }
        else
        {
            throw new Exception('Could not find that route');
        }

        return array('distance' => $distance, 'time' => $time);
    }
    else
    {
        throw new Exception('Could not resolve URL');
    }
}


try
{
    //$info = get_driving_information('44-46 St. John Street London', '15 Manor Road, Inskip, Preston');
    $info = get_driving_information('via Tiburtina 538, Roma', 'via Ariosto 25, Roma');
    echo $info['distance'].' miles '.$info['time'].' seconds';
}
catch(Exception $e)
{
    echo 'Caught exception: '.$e->getMessage()."\n";
}