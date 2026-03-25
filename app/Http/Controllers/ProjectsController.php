<?php

namespace App\Http\Controllers;

use App\Enums\AuditTrailsActionsEnum;
use App\Enums\AuditTrailsEntityTypeEnum;
use App\Models\Projects;
use App\Http\Requests\StoreProjectsRequest;
use App\Http\Requests\UpdateProjectsRequest;
use App\Models\AuditTrail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    use AuthorizesRequests;
    /**
     * TODO agregar permisos y request
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Projects::class);

        $query = Projects::with('createdBy')->with('modules')->with('domains')->with('artifacts');
        if ($projects_id = $request->query('projects_id')) {
            $query->where('projects_id', $projects_id);
        }
        if ($projects_client_name = $request->query('client_name')) {
            $query->where('client_name', 'like', '%' . $projects_client_name . '%');
        }
        if ($projects_status = $request->query('status')) {
            $query->where('status', $projects_status);
        }
        if ($request->query('is_archived')) {
            $query->where('is_archived', $request->query('is_archived'));
        }
        if ($projects_created_by_id = $request->query('created_by_id')){
            $query->where('created_by', $projects_created_by_id);
        }

        $order_by = $request->query('order_by', 'id');
        $order_dir = $request->query('order_dir', 'desc');

        if ($per_page = $request->query('per_page')) {
            $projects = $query->orderBy($order_by, $order_dir)->paginate($per_page);
        } else {
            $projects = $query->orderBy($order_by, $order_dir)->get();
        }
        //$payment = Payment::all();
        return response()->json($projects);
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
    public function store(StoreProjectsRequest $request)
    {
        $this->authorize('create', Projects::class);

        $data = $request->validated();

        // Set created_by to the authenticated user's id
        $data['created_by'] = $request->user()->id;

        $projects = Projects::create($data);
        AuditTrail::logAction(
            $request->user()->id,
            AuditTrailsEntityTypeEnum::PROJECT->value,
            $projects->id,
            AuditTrailsActionsEnum::CREATED->value,
            null,
            null
        );
        return response()->json($projects, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $projects)
    {
        $this->authorize('view', $projects);

        return response()->json($projects);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projects $projects)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectsRequest $request, Projects $projects)
    {
        $this->authorize('update', [$projects, $request]);

        $projects->update($request->validated());
        AuditTrail::logAction(
            $request->user()->id,
            AuditTrailsEntityTypeEnum::PROJECT->value,
            $projects->id,
            AuditTrailsActionsEnum::UPDATED->value,
            null,
            null
        );
        return response()->json([
            'message' => 'Project update successfully',
            'project' => $projects
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $projects)
    {
        $this->authorize('delete', $projects);
        $beforeData = $projects->toArray();
        $projects->delete();
        AuditTrail::logAction(
            request()->user()->id,
            AuditTrailsEntityTypeEnum::PROJECT->value,
            $projects->id,
            AuditTrailsActionsEnum::DELETED->value,
            $beforeData,
            null
        );
        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }
}
