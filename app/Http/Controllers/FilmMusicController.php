<?php

namespace App\Http\Controllers;

use App\Models\FilmMusic;
use App\Http\Requests\StoreFilmMusicRequest;
use App\Http\Requests\UpdateFilmMusicRequest;

class FilmMusicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFilmMusicRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFilmMusicRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FilmMusic  $filmMusic
     * @return \Illuminate\Http\Response
     */
    public function show(FilmMusic $filmMusic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FilmMusic  $filmMusic
     * @return \Illuminate\Http\Response
     */
    public function edit(FilmMusic $filmMusic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFilmMusicRequest  $request
     * @param  \App\Models\FilmMusic  $filmMusic
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFilmMusicRequest $request, FilmMusic $filmMusic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FilmMusic  $filmMusic
     * @return \Illuminate\Http\Response
     */
    public function destroy(FilmMusic $filmMusic)
    {
        //
    }
}
