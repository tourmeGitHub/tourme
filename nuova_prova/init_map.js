/**
 * Created by biagiomontesano on 23/11/15.
 */

function initMap() {
    // Google services
    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;

    // map's parameters
    var map = new google.maps.Map
    (
        document.getElementById('map'),

        {
            zoom: 2,
            center: {lat: 41.85, lng: -87.65}
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
                var arr = JSON.parse(xmlhttp.response);
                calculateAndDisplayRoute(directionsService, directionsDisplay, arr);
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
    xmlhttp.send (null);
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
            optimizeWaypoints: true,
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