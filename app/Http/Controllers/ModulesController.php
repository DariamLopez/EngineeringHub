<?php

namespace App\Http\Controllers;

use App\Models\Modules;
use App\Http\Requests\StoreModulesRequest;
use App\Http\Requests\UpdateModulesRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //$this->authorize('viewAny', Artifacts::class);

        $query = Modules::query()->with('project');
        if ($project_id = $request->query('project_id')) {
            $query->where('project_id', $project_id);
        }
        if ($domain_id = $request->query('domain_id')) {
            $query->where('domain_id', $domain_id);
        }
        if ($modules_name = $request->query('name')) {
            $query->where('name', $modules_name);
        }
        if ($modules_status = $request->query('status')) {
            $query->where('status', $modules_status);
        }

        $order_by = $request->query('order_by', 'id');
        $order_dir = $request->query('order_dir', 'desc');

        if ($per_page = $request->query('per_page')) {
            $modules = $query->orderBy($order_by, $order_dir)->paginate($per_page);
        } else {
            $modules = $query->orderBy($order_by, $order_dir)->get();
        }
        //$modules = Modules::all();
        return response()->json($modules);
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
    public function store(StoreModulesRequest $request)
    {
        $this->authorize('create', Modules::class);
         $modules = Modules::create($request->validated());
         return response()->json([
             'message' => 'Module created successfully',
             'data' => $modules
         ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Modules $modules)
    {
        $this->authorize('view', $modules);
        return response()->json($modules);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Modules $modules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModulesRequest $request, Modules $modules)
    {
        $this->authorize('update', [$modules, $request]);
        $modules->update($request->validated());
        return response()->json([
            'message' => 'Module updated successfully',
            'data' => $modules
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modules $modules)
    {
        $this->authorize('delete', $modules);
        $modules->delete();
        return response()->json([
            'message' => 'Module deleted successfully'
        ]);
    }
}
