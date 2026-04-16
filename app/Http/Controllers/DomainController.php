<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyMassiveDomainRequest;
use App\Models\Domain;
use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\StoreMassiveDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Http\Requests\UpdateMassiveDomains;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //$this->authorize('viewAny', Domain::class);

        //$project_id = $request->validated('project_id');
        //Log::info("Fetching domains for project_id: $project_id with filters: " . json_encode($request->validated()));
        $query = Domain::query()->with('modules')->with('owner')->with('project')->where('project_id', $request->query('project_id'));
        if ($domain_name = $request->query('name')) {
            $query->where('name', 'like', "%$domain_name%");
        }
        if ($domain_owner = $request->query('owner_user_id')) {
            $query->where('owner_user_id', $domain_owner);
        }
        $order_by = $request->query('order_by', 'id');
        $order_dir = $request->query('order_dir', 'desc');

        if ($per_page = $request->query('per_page')) {
            $domain = $query->orderBy($order_by, $order_dir)->paginate($per_page);
        } else {
            $domain = $query->orderBy($order_by, $order_dir)->get();
        }
        return response()->json($domain);
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
     * Store multiple newly created resources in storage.
     */
    public function massiveStore(StoreMassiveDomainRequest $request)
    {
        $domainsData = $request->validated();
        Log::info("Received request to create multiple domains for project_id: {$domainsData['project_id']} with data: " . json_encode($domainsData['domains']));
        $projectId = $domainsData['project_id'];
        $createdDomains = [];

        foreach ($domainsData['domains'] as $domainData) {
            $createdDomains[] = Domain::create(array_merge($domainData, ['project_id' => $projectId]));
        }

        return response()->json($createdDomains, 201);
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
     * Update multiple resources in storage.
     */
    public function massiveUpdate(UpdateMassiveDomains $request)
    {
        Log::info("Received request to update multiple domains with data: " . json_encode($request->input('domains')));
        $data = $request->validated();
        $updatedDomains = [];
        foreach ($data['domains'] as $domainData) {
            $domain = Domain::find($domainData['id']);
            if ($domain) {
                $domain->update($domainData);
                $updatedDomains[] = $domain;
            }
        }

        return response()->json([
            'message' => 'Domains updated successfully',
            'domains' => $updatedDomains
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
    public function massiveDestroy(DestroyMassiveDomainRequest $request)
    {
        Log::info("Received request to delete multiple domains for project_id: {$request->input('project_id')} with domain_ids: " . json_encode($request->input('domain_ids')));
        $data = $request->validated();
        $deletedCount = Domain::where('project_id', $data['project_id'])->whereIn('id', $data['domain_ids'])->delete();
        return response()->json([
            'message' => "Deleted $deletedCount domains for project_id: {$data['project_id']}"
        ], 200);
    }
}
