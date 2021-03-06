<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Same as above but with pagination (showing ten posts per page) and ordered by created_at in ascending order
        $posts = Post::orderBy('created_at', 'desc')->paginate(10);

        // The view that displays all of the posts
        return view('posts.index')->with('posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Form validation
        $this->validate($request, [

            'title' => 'required',
            'location' => 'required',
            'marker_latitude' => 'required',
            'marker_longitude' => 'required',
            'fire_status_button' => 'required',
            'marker_icon' => 'required',
            'auth_status_button' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999', // Optional ability to upload an image

        ]);
        
        // Handle File Upload
        if($request->hasFile('cover_image')) {

            // Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();

            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            // Get just extension
            $extension = $request->file('cover_image')->getClientOriginalExtension();

            // Filename to store
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            // Upload the image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);

        }

        else {

            // If user does not upload an image use this as default name for database
            $fileNameToStore = 'noimage.jpg';

        }

        // Create post
        $post = new Post;

        // Input fields
        $post->title = $request->input('title');
        $post->location = $request->input('location');
        $post->marker_latitude = $request->input('marker_latitude');
        $post->marker_longitude = $request->input('marker_longitude');
        $post->fire_status_button = $request->input('fire_status_button');
        $post->marker_icon = $request->input('marker_icon');
        $post->auth_status_button = $request->input('auth_status_button');
        $post->body = $request->input('body');

        // From auth
        $post->user_id = auth()->user()->id;

        // Image input
        $post->cover_image = $fileNameToStore;

        // Saves the post
        $post->save();

        return redirect('/posts')->with('success', 'Your fire sighting has been successfully submitted!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // The variable that finds the id for the post from the database
        $post = Post::find($id);

        // The view for that post
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // The variable that finds the id for the post from the database
        $post = Post::find($id);

        // Check for correct user
        if(auth()->user()->id !== $post->user_id){

            // If user is not authorized to edit the post redirect them back to viewing posts
            return redirect('/posts')->with('error', 'Unauthorized Page');

        }

        // The view for that post to be edited
        return view('posts.edit')->with('post', $post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Form validation
        $this->validate($request, [

            'title' => 'required',
            'location' => 'required',
            'marker_latitude' => 'required',
            'marker_longitude' => 'required',
            'fire_status_button' => 'required',
            'marker_icon' => 'required',
            'auth_status_button' => 'required',
            'body' => 'required'

        ]);

        // Handle File Upload
        if($request->hasFile('cover_image')) {

            // Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();

            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            // Get just extension
            $extension = $request->file('cover_image')->getClientOriginalExtension();

            // Filename to store
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            // Upload the image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);

        }

        // Update post
        $post = Post::find($id);

        // Input fields
        $post->title = $request->input('title');
        $post->location = $request->input('location');
        $post->marker_latitude = $request->input('marker_latitude');
        $post->marker_longitude = $request->input('marker_longitude');
        $post->fire_status_button = $request->input('fire_status_button');
        $post->marker_icon = $request->input('marker_icon');
        $post->auth_status_button = $request->input('auth_status_button');
        $post->body = $request->input('body');
        // Checks to see if a new image has been uploaded
        if($request->hasFile('cover_image')) {

            $post->cover_image = $fileNameToStore;

        };
        $post->save();

        return redirect('/posts')->with('success', 'Your sighting has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // The variable that finds the id for the post from the database
        $post = Post::find($id);

        // Check for correct user
        if(auth()->user()->id !== $post->user_id){

            // If user is not authorized to delete the post redirect them back to viewing posts
            return redirect('/posts')->with('error', 'Unauthorized Page');

        }

        // Deletes the image
        if($post->cover_image != 'noimage.jpg'){

            Storage::delete('public/cover_images/' . $post->cover_image);

        }

        // Deletes the post
        $post->delete();

        return redirect('/posts')->with('success', 'Your sighting has been removed');
    }
}
