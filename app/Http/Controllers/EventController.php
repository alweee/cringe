<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use TomLingham\Searchy\Facades\Searchy;

use App\Event;
require(__DIR__ . '/maps.php');

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::all();

        return view('events.index', compact('events'));
    }

    public function search(Request $request)
    {

        // $events = Searchy::events('title', 'description','category')->query(request('search'))->get();

        $events = Event::hydrate((array)Searchy::driver('simple')->events('title', 'description','category')->query(request('search'))->get()->toArray());



       // dd((array) $events);

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request()->all());

        $key = 'AIzaSyDyyd0zM6OUe4PflYQ1_BD-feq3omU9zK0';

        $search = implode(', ', [$request['address'], $request['number'], $request['zip']]);

        $geoData = google_maps_search($search, $key);

        $mapData = map_google_search_result($geoData);

        Event::create([
            'provider_id' => $request->user('provider')->id,
            'title' => request('title'),
            'date' => request('date'),
            'price' => request('price') ,
            'description' => request('description') ,
            'ages' => request('ages'),
            'category' => request('category'),
            'availability' => request('availability'),
            'sold' => 0,
            'city' => request('city'),
            'address' => request('address'),
            'number' => request('number'),
            'zip' => request('zip'),
            'lat' => $mapData['lat'], 
            'long' => $mapData['lng'],
        ]);

        return redirect('/events');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    public function stats()
    {
        $my_id = Auth::guard('provider')->user()->id;
        $events = Event::where('provider_id', $my_id)->get();
        return view('events.stats', compact('events'));
    }

    public function readData(Request $request){
        $my_id = Auth::guard('provider')->user()->id;
         if ($request -> ajax()){
            $events= Event::where([['provider_id', $my_id],['date', '>=' , $request->start],['date', '<' ,  $request->end]])->get();
            return response()->json($events);         
        }

         //$msg = "This is a simple message.";
         //$events = Event::orderBy('date')->get();
         //return response()->json($events);
    }

    
}
