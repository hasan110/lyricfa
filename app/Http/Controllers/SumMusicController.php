<?php

namespace App\Http\Controllers;

use App\Models\SumMusic;
use App\Http\Requests\StoreSumMusicRequest;
use App\Http\Requests\UpdateSumMusicRequest;

class SumMusicController extends Controller
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
     * @param  \App\Http\Requests\StoreSumMusicRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSumMusicRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SumMusic  $sumMusic
     * @return \Illuminate\Http\Response
     */
    public function show(SumMusic $sumMusic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SumMusic  $sumMusic
     * @return \Illuminate\Http\Response
     */
    public function edit(SumMusic $sumMusic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSumMusicRequest  $request
     * @param  \App\Models\SumMusic  $sumMusic
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSumMusicRequest $request, SumMusic $sumMusic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SumMusic  $sumMusic
     * @return \Illuminate\Http\Response
     */
    public function destroy(SumMusic $sumMusic)
    {
        //
    }
}
