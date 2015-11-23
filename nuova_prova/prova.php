<?php
//$verifica = $_GET["anno"];
$fields=array('Lat','Lon');
$nodedata = array_fill_keys($fields, '');


    $nodedata[0]= array("Lat" => "41.91129" , "Lon" => "12.54815", "Name" => "Nome1" , "Building" => "Building1", "Religion" => "Religion1" , "Wikipedia" => "www.Wikipedia.it");
$nodedata[1]= array("Lat"=> "41.8821640","Lon"=> "12.5186234","Name" => "Nome2" , "Building" => "Building2", "Religion" => "Religion2" , "Wikipedia" => "www.Wikipedia.it");
$nodedata[2]= array("Lat"=> "41.8875465","Lon"=> "12.5146132","Name" => "Nome3" , "Building" => "Building3", "Religion" => "Religion3" , "Wikipedia" => "www.Wikipedia.it");
$nodedata[3]= array("Lat"=> "41.8875694","Lon"=> "12.5147038","Name" => "Nome4" , "Building" => "Building4", "Religion" => "Religion4" , "Wikipedia" => "www.Wikipedia.it");
$nodedata[4]= array( "Lat"=> "41.8876457","Lon"=> "12.5145779","Name" => "Nome5" , "Building" => "Building5", "Religion" => "Religion5" , "Wikipedia" => "www.Wikipedia.it");
$nodedata[5]= array( "Lat"=> "41.8876648","Lon"=> "12.5146723","Name" => "Nome6" , "Building" => "Building6", "Religion" => "Religion6" , "Wikipedia" => "www.Wikipedia.it");
//$nodedata[6]= array(  "Lat"=> "41.8875771","Lon"=> "12.5162363","Name" => "Nome7" , "Building" => "Building7", "Religion" => "Religion7" , "Wikipedia" => "www.Wikipedia.it");
//$nodedata[7]= array(  "Lat"=> "41.8875771","Lon"=> "12.5162544", "Name" => "Nome8" , "Building" => "Building8", "Religion" => "Religion8" , "Wikipedia" => "www.Wikipedia.it");
//$nodedata[8]= array(  "Lat"=> "41.8876305","Lon"=> "12.5160761", "Name" => "Nome9" , "Building" => "Building9", "Religion" => "Religion9" , "Wikipedia" => "www.Wikipedia.it"););
//$nodedata[9]= array(   "Lat"=> "41.8876381","Lon"=> "12.5160875","Name" => "Nome10" , "Building" => "Building10", "Religion" => "Religion10" , "Wikipedia" => "www.Wikipedia.it");
//$nodedata[10]= array(   "Lat"=> "41.8878021","Lon"=> "12.5156097", "Name" => "Nome11" , "Building" => "Building11", "Religion" => "Religion11" , "Wikipedia" => "www.Wikipedia.it");
//$nodedata[11]= array(   "Lat"=> "41.8878136","Lon"=> "12.5155048", "Name" => "Nome12" , "Building" => "Building12", "Religion" => "Religion12" , "Wikipedia" => "www.Wikipedia.it");

echo json_encode($nodedata);


?>