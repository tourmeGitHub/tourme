/**
 * Created by biagiomontesano on 23/11/15.
 */

var map = null;
var markers = new Array();
var coordinates = new Array();
var directionsDisplay = new Array();
var directionsService = new google.maps.DirectionsService();

function placeMarker(lat, lng){
    var markerPos = new google.maps.LatLng(lat, lng);
    var marker = new google.maps.Marker
    (
        {
            position: markerPos,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        }
    );

    markers.push(marker);
}

function removeRoutes(){
    for(var i = 0; i &lt; directionsDisplay.length; i++)
    {
        directionsDisplay[i].setMap(null);
    }
    
    directionsDisplay = new Array();
}

function drawRoute(begin, end, waypts){
    var request =
    {
        origin: begin,
        destination: end,
        waypoints: waypts,
        optimizeWaypoints: true,
        travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route
    (
        request, 
        function(response, status)
        {
            if (status === google.maps.DirectionsStatus.OK)
            {
                var dirDisp = new google.maps.DirectionsRenderer({suppressMarkers: true});
                dirDisp.setMap(map);
                dirDisp.setDirections(response);
                directionsDisplay.push(dirDisp);
            }
        }
    );
}

function calculateRoutes(){
    removeRoutes();
    var begin = new google.maps.LatLng(coordinates[0][0], coordinates[0][1]);
    var end = null;
    var waypts = new Array();
    var wCount = 0;
    var REQUEST_WAYPOINTS_LIMIT = 8;
    var i;
    for(i = 1; i < (coordinates.length-1); i++)
    {
        if(wCount === REQUEST_WAYPOINTS_LIMIT)
        {
            i++;
            end = new google.maps.LatLng(coordinates[i][0], coordinates[i][1]);
            drawRoute(begin, end, waypts);
            begin = end;
            wCount = 0;
            waypts = new Array();
        }


        waypts.push
        (
            {
            location: new google.maps.LatLng(coordinates[i][0], coordinates[i][1]),
            stopover: true
            }
        );
        wCount++;
    }

    if(waypts.length &gt; 0)
    {
        end = new google.maps.LatLng(coordinates[coordinates.length-1][0], coordinates[coordinates.length-1][1]);
        drawRoute(begin, end, waypts);
    }

    for(var i = 0; i &lt; coordinates.length; i++)
    {
        placeMarker(coordinates[i][0], coordinates[i][1]);
    }
}