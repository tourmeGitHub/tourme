/**
 * Created by biagiomontesano on 23/11/15.
 */

var coordinates = [];
var interest_points = [];
var map = null;
var markers = [];
var directionsDisplay = [];
var directionsService = new google.maps.DirectionsService();
var REQUEST_WAYPOINTS_LIMIT = 8;

function removeRoutes()
{
    for(var i = 0; i<directionsDisplay.length; i++)
    {
        directionDisplay[i].setMan(null);
    }
    
    directionDisplay = [];
}

function placeMarker(lat, lon, icon_path)
{
    var markerPos = new google.maps.LatLng(lat, lon);
    var marker = new google.maps.Marker
    (
        {
            position: markerPos,
            map: map,
            animation: google.maps.Animation.DROP,
            icon: icon_path
        }
    );

    markers.push(marker);
}

function placeAttractionMarker(lat, lon, icon_path, name, url)
{
    //generate marker's info
    var contentString = '<div id="content"><div id="siteNotice">' +
    '</div><div id="bodyContent"><h4 id="firstHeading" class="firstHeading">' +  name + '</h4>' +
        '<a href="' + url + '" style="text-decoration:none" target="_blank"><b>Sito web</b></a></div></div>';


    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });

    var markerPos = new google.maps.LatLng(lat, lon);
    var marker = new google.maps.Marker
    (
        {
            position: markerPos,
            map: map,
            animation: google.maps.Animation.DROP,
            icon: icon_path,
            title: name
        }
    );

    marker.addListener('click', function() {
        infowindow.open(map, marker);
    });

    markers.push(marker);
}

function drawRoute(begin, end, waypts)
{
    var request =
    {
        origin: begin,
        destination: end,
        waypoints: waypts,
        optimizeWaypoints: true,
        travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route
    (
        request,
        function(response, status)
        {
            if(status === google.maps.DirectionsStatus.OK)
            {
                //directionsDisplay.setDirections(response);
                var dirDisp = new google.maps.DirectionsRenderer({suppressMarkers: true});
                dirDisp.setMap(map);
                dirDisp.setDirections(response);
                directionsDisplay.push(dirDisp);
            }
        }
    );
}

function calculateRoutes()
{
    // remove previous route
    removeRoutes();
    //window.alert('Numero punti ' + coordinates.length);

    // set start position
    var begin = new google.maps.LatLng(coordinates[0].Lat, coordinates[0].Lon);

    // set end position
    var end = null;

    // waypoints struct
    var waypoints = [];

    // counter
    var wCount = 0;

    // loop
    var i;
    for(i=1; i<coordinates.length-1; i++)
    {
        if(wCount === REQUEST_WAYPOINTS_LIMIT)
        {
            // increment counter to get end's index
            i++;

            // set end
            end = new google.maps.LatLng(coordinates[i].Lat, coordinates[i].Lon);

            // draw current route
            drawRoute(begin, end, waypoints);

            // update indeces
            begin = end;

            // reset structures
            wCount = 0;
            waypoints = [];
        }

        // add waypoint to list
        waypoints.push
        (
            {
                location: new google.maps.LatLng(coordinates[i].Lat, coordinates[i].Lon),
                stopover: true
            }
        );

        // increments counter
        wCount++;
    }

    if(waypoints.length > 0)
    {
        end = new google.maps.LatLng(coordinates[coordinates.length-1].Lat, coordinates[coordinates.length-1].Lon);
        drawRoute(begin, end, waypoints);
    }

    for(var i = 0; i<coordinates.length; i++)
    {
        placeMarker(coordinates[i].Lat, coordinates[i].Lon, "http://gmaps-samples.googlecode.com/svn/trunk/markers/red/blank.png");
    }

    for(var j = 0; j<interest_points.length; j++)
    {
        placeAttractionMarker(
            interest_points[j].Lat,
            interest_points[j].Lon,
            "http://gmaps-samples.googlecode.com/svn/trunk/markers/orange/blank.png",
            interest_points[j].Name,
            interest_points[j].Wikipedia
        );
    }

}

function initMap() {
    // Google services
    var directionsDisplay = new google.maps.DirectionsRenderer;
    var mapProp;

    // map's parameters
    map = new google.maps.Map
    (
        document.getElementById('map'),

        {
            zoom: 2,
            //center: {lat: 41.85, lng: -87.65}
            center: new google.maps.LatLng(41.85, -87.65)
        }
    );

    // display map
    directionsDisplay.setMap(map);

    // create Http request
    var xmlhttp = new XMLHttpRequest ();

    // state change function
    xmlhttp.onreadystatechange = function ()
    {
        if (xmlhttp.readyState == 4)
        {
            if (xmlhttp.status == 200)
            {
                //coordinates = JSON.parse(xmlhttp.response);
                var received_data = JSON.parse(xmlhttp.response);
                interest_points = received_data[0];
                coordinates = received_data[1];
                //calculateAndDisplayRoute(directionsService, directionsDisplay, coordinates);
                calculateRoutes();
                window.alert('Lat ' + coordinates.length);
            }

            else
            {
                document.getElementById('id01').innerHTML = 'Ops... qualcosa non ha funzionato - Stato HTTP: '
                    + xmlhttp.status;
            }
        }

        else
        {
            // NOPE
        }
    };

    xmlhttp.open ('GET', 'search_points.php?start_loc=' + getValue("start_loc") + '&end_loc=' + getValue("end_loc"));
    //xmlhttp.open ('GET', 'prova.php', false);
    xmlhttp.send ();
}


function calculateAndDisplayRoute(directionsService, directionsDisplay,response)
{
    // declaring variables
    var waypts = [];
    var arr = response;
    //window.alert('Numero elementi in arr ' + Object.keys(arr).length);
    var result = "";

    // populate waypoints array
    for (var a in arr)
    {
        if (arr[a].Lat != undefined)
        {
            var lar = String(arr[a].Lat);
            var lon = String(arr[a].Lon);
            /*            var lar = String(arr[a]["Lat"]);
             var lon = String(arr[a]["Lon"]);*/

            waypts.push
            (
                {
                    location: lar + "," + lon,
                    stopover: true
                }
            );
        }
    }


    //var start = new google.maps.LatLng(42.327463, -87.973640);
    directionsService.route
    (
        {
            origin: getValue("start_loc"),
            destination: getValue("end_loc"),
            waypoints: waypts,
            //optimizeWaypoints: true,
            travelMode: google.maps.TravelMode.DRIVING
        },

        function(response, status)
        {
            if (status === google.maps.DirectionsStatus.OK)
            {
                directionsDisplay.setDirections(response);
                /*                     var route = response.routes[0];
                 var summaryPanel = document.getElementById('directions-panel');
                 summaryPanel.innerHTML = '';

                 // For each route, display summary information.
                 for (var i = 0; i < route.legs.length; i++)
                 {
                 var routeSegment = i + 1;
                 summaryPanel.innerHTML += '<b>Route Segment: ' + routeSegment +
                 '</b><br>';
                 summaryPanel.innerHTML += route.legs[i].start_address + ' to ';
                 summaryPanel.innerHTML += route.legs[i].end_address + '<br>';
                 summaryPanel.innerHTML += route.legs[i].distance.text + '<br><br>';
                 }*/
            }

            else
            {
                window.alert('Directions request failed due to ' + status);
            }
        }
    );
}


google.maps.event.addDomListener(window, 'load', initMap);
/*
function populateMap(directionsService, directionsDisplay)
{
    var init_pos = 0;
    var pointer = 0;

    if(init_pos == 2)
    {

    }
}*/
