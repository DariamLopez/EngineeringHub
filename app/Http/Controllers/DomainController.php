<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $domains = Domain::with('owner', 'project')->get();
        return response()->json($domains);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDomainRequest $request)
    {
        $domain = Domain::create($request->validated());
        return response()->json($domain, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain)
    {
        return response()->json($domain);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domain $domain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        $domain->update($request->validated());
        return response()->json([
            'message' => 'Domain updated successfully',
            'domain' => $domain
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain)
    {
        $domain->delete();
        return response()->json([
            'message' => 'Domain deleted successfully'
        ], 200);
    }
}
