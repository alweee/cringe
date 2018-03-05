@extends ('layout')

@section ('content')
<link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script>

function getLoc(){
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(setPos);
    } else {
        //document.getElementById("new").style.color = "red";
    }
}

function setPos(position){
    document.getElementById("lat").value = position.coords.latitude;
    document.getElementById("lng").value = position.coords.longitude;
    //alert(document.getElementById("lat").value + "," + document.getElementById("lng").value);
}

var map;
var coords = [];
var markers = [];
var zoom= {{ $zoom }};

function storeCoord(id, lat, lng, title, descr){
    coords.push({id: id, lat:lat, lng: lng, title: title, description: descr});
}

function initMap() {
    var initPos= {lat: {{ $latlng['lat'] }}, lng: {{ $latlng['lng'] }}};
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: zoom,
        center: initPos
    });
    if (zoom ==15){
        var marker = new google.maps.Marker({
            position: initPos,
            animation: google.maps.Animation.DROP,
            title: "Your Location"
        });
        marker.addListener('click', function() {
        //    map.setZoom(15);
        //    map.setCenter(marker.getPosition());
        //    toggleBounce(-10);
        });
        marker.setIcon('http://maps.google.com/mapfiles/ms/icons/green-dot.png');
        marker.setMap(map);
        markers.push({id: -10, marker:marker});
    }
    for (var i = 0; i < coords.length; i++) {
        addMarker(coords[i]);
    }
}
var title= '<div id="content"> <div id="siteNotice"> </div><h1 id="firstHeading" class="firstHeading">';
var main= '</h1> <div id="bodyContent"><p>';
var link= '</p><p><a href="';
var end=   '">More Info...</a></p></div></div>';

var infowindows=[];
function addMarker(pos){
    var info= title + pos.title + main + pos.description + link + "/events/"+ pos.id + end;
    console.log(info);
    var marker = new google.maps.Marker({
        position: pos,
        animation: google.maps.Animation.DROP,
        title: pos.title
    });
    marker.addListener('click', function() {
        map.setZoom(15);
        map.setCenter(marker.getPosition());
    });
    var infowindow = new google.maps.InfoWindow({
          content: info,
          maxWidth: 320
      });
    marker.setIcon('http://maps.google.com/mapfiles/ms/icons/blue-dot.png');
    marker.setMap(map);
    marker.addListener('click', function() {
        for (i=0; i< infowindows.length; i++) infowindows[i].close();
          infowindow.open(map, marker);
        });

    markers.push({id: pos.id, marker:marker} );
    infowindows.push(infowindow);
}
function toggleBounce(id) {
    var m;
    for (var i = 0; i < markers.length; i++) {
        if(markers[i].id== id) {
            m= markers[i];
        }else {
            markers[i].marker.setAnimation(null);
        }
    }
    if (m.marker.getAnimation() !== null) {
        m.marker.setAnimation(null);
    } else {
        m.marker.setAnimation(google.maps.Animation.BOUNCE);
    }
    return m;
}

function resetCenter(id){
    var m= toggleBounce(id);
    map.setZoom(15);
    map.setCenter(m.marker.getPosition());
    google.maps.event.trigger(m.marker, 'click')

}
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
            <form method="GET" action="/events/search" class="search-form">
        <!-- {{ csrf_field() }} -->

    <div id= "big">
        <div class="form-group has-feedback">
            <label for="search" class="sr-only">Search</label>
            <input type="text" class="form-control" name="search" id="search" placeholder="Search" autocomplete="off">
            <span class="glyphicon glyphicon-search form-control-feedback"></span>
        </div>


        <div id="filters" style="display: none;">

            <div class="form-group">
                    <input type="number" class="form-control" name="age" id="age" placeholder="Age" autocomplete="off">
            </div>

            <div class="form-group">
                    <input type="number" class="form-control" name="max_price" id="max_price" placeholder="Maximum Price" autocomplete="off">
            </div>

            <div class="form-group">
                    <input type="number" class="form-control" name="radius" id="radius" placeholder="Radius in kilometers e.g. 5" autocomplete="off">
            </div>

        </div>
    </div>
<!-- @if (Auth::guard('human')->user())
        <label class="radio-inline">
          <input type="radio" name="location" value="default" checked="checked">Use my default location
        </label>
@endif -->
        <!-- <label class="radio-inline "> -->
        <button type="button" class="btn btn-primary btn-sm" onclick="getLoc()">
                  <span class="glyphicon glyphicon-refresh"></span></button>
        <input type="checkbox" name="location" value="new" id= "new" onclick="check()">Use my current location
        <!-- </label> -->

        <div class="form-group">
                <input class="form-control" name="lat" id="lat" style="display:none">
        </div>
        <div class="form-group">
                <input class="form-control" name="lng" id="lng" style="display:none">
        </div>
        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary" style="display:none">
                    Search
                </button>
            </div>
        </div>


    </form>
            <div class="panel panel-default">

                <div class="panel-heading">Events</div>
                @foreach ($events as $event)
                <button type="button" class="btn btn-default list-group-item list-group-item-action flex-column align-items-start" onclick="resetCenter({{$event['id']}})">
                    <div class="">
                        <h2><a target="_blank" href="/events/{{ $event['id'] }}" style="font-weight:1000; color:black" >{{ $event['title'] }}</a></h2>
                        <p> <?php
                        $desc = substr($event['description'], 0, 40);
                        echo $desc;
                        ?>

                    </div>
                </button>
                <script type="text/javascript">
                // addMarker(37.9926033,23.75873,"aaaaaa");
                storeCoord({{ $event['id'] }}, {{ $event['lat'] }},{{ $event['long'] }},"{{ $event['title'] }}", "{{ $desc }}");
                </script>
                @endforeach
            </div>
        </div>
        <div class="col-md-1" ></div>
        <div class="row">
            <div class="col-md-6">
                <div class="affix col-md-11">
           <div id="map" class="col-md-6" style="height:70vh" >
           </div>
            </div>

    </div>

    </div>
</div>
</div>

<style media="screen">

@media only screen and (max-width: 900px) {
    .affix {
    position: static;
}
#myBtn{
    display:none;
}
}

</style>
<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDExc4GNJctRKQDUNuYvUm6CtUVXid8eVo&callback=initMap">
</script>
<script type="text/javascript">
$(document).on('click','body',function(){
    //alert(document.activeElement.tagName);
    if (document.activeElement.tagName == "INPUT"){
        document.getElementById("filters").style.display= "block";
    }
    else{
        document.getElementById("filters").style.display= "none";

    }
});
var checked;
$(document).ready(function() {
    if ( {{ $zoom }} == 15)  {
        $("#new").prop("checked", true);
        document.getElementById("lat").value= {{ $latlng['lat'] }}
        document.getElementById("lng").value={{ $latlng['lng'] }}
        checked=1;
    }
    else{
        checked=0;  // unchecked by default
    }
});
function check(){
    if (checked==1){
        document.getElementById("lat").value= "";
        //tote egine unchecked
        document.getElementById("lng").value= "";
    }
    else {
        document.getElementById("lat").value= {{ $latlng['lat'] }}
        document.getElementById("lng").value={{ $latlng['lng'] }}
    }
}
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
@endsection
