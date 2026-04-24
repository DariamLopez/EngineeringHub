<?php

namespace App\Http\Controllers;

use App\Enums\AuditTrailsActionsEnum;
use App\Enums\AuditTrailsEntityTypeEnum;
use App\Enums\ModuleStatusEnum;
use App\Http\Requests\DestroyMassiveModulesRequest;
use App\Http\Requests\StoreMassiveModulesRequest;
use App\Models\Modules;
use App\Http\Requests\StoreModulesRequest;
use App\Http\Requests\UpdateMassiveModules;
use App\Http\Requests\UpdateModulesRequest;
use App\Models\AuditTrail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModulesController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //$this->authorize('viewAny', Artifacts::class);

        $query = Modules::query()->with('project')->with('domain');
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
        AuditTrail::logAction(
            $request->user()->id,
            AuditTrailsEntityTypeEnum::MODULE->value,
            $modules->id,
            AuditTrailsActionsEnum::CREATED->value,
            null,
            $modules->toArray()
        );
        return response()->json([
            'message' => 'Module created successfully',
            'data' => $modules
        ], 201);
    }

    public function massiveStore(StoreMassiveModulesRequest $request)
    {
        $this->authorize('create', Modules::class);
        $data = $request->validated();

        $createdModules = [];
        foreach ($data['modules'] as $moduleData) {
            $module = Modules::create([
                'project_id' => $data['project_id'],
                'name' => $moduleData['name'],
                'domain_id' => $moduleData['domain_id'],
                'priority' => $moduleData['priority'],
                'phase' => $moduleData['phase'],
                'status' => ModuleStatusEnum::DRAFT->value,
            ]);
            $createdModules[] = $module;
            AuditTrail::logAction(
                $request->user()->id,
                AuditTrailsEntityTypeEnum::MODULE->value,
                $module->id,
                AuditTrailsActionsEnum::CREATED->value,
                null,
                $module->toArray()
            );
        }

        return response()->json([
            'message' => 'Modules created successfully',
            'modules' => $createdModules
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Modules $modules)
    {
        $this->authorize('view', $modules);
        $modules->load(['project', 'domain']);
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
        $action = '';
        if ($request->validated('status') == ModuleStatusEnum::VALIDATED->value) {
            $action = AuditTrailsActionsEnum::VALIDATED->value;
        } else {
            $action = AuditTrailsActionsEnum::UPDATED->value;
        }
        $old_module = $modules->replicate();
        $old_module->id = $modules->id;
        $modules->update($request->validated());
        Log::info("Old module data: " . json_encode($old_module->toArray()));
        Log::info("New module data: " . json_encode($modules->toArray()));
        AuditTrail::logAction(
            $request->user()->id,
            AuditTrailsEntityTypeEnum::MODULE->value,
            $modules->id,
            AuditTrailsActionsEnum::from($action)->value,
            $old_module->toArray(),
            $modules->toArray()
        );
        return response()->json([
            'message' => 'Module updated successfully',
            'data' => $modules
        ]);
    }

    public function massiveUpdate(UpdateMassiveModules $request)
    {
        /* Log::info("Received request to update multiple modules with data: " . json_encode($request->input())); */
        $data = $request->validated();
        /* Log::info("Received request to update multiple modules with data: " . json_encode($data)); */
        $moduleIds = array_column($data['modules'], 'id');
        $modules = Modules::findMany($moduleIds)->keyBy('id');

        // Authorize each module before making any changes
        foreach ($data['modules'] as $moduleData) {
            $module = $modules->get($moduleData['id']);
            if ($module) {
                $moduleRequest = Request::create('/', 'POST', $moduleData);
                $this->authorize('update', [$module, $moduleRequest]);
            }
        }

        $updatedModules = [];
        foreach ($data['modules'] as $moduleData) {
            $module = $modules->get($moduleData['id']);
            if ($module) {
                $old_module = $module->replicate();
                $old_module->id = $module->id;
                $module->update($moduleData);
                $updatedModules[] = $module;
                AuditTrail::logAction(
                    request()->user()->id,
                    AuditTrailsEntityTypeEnum::MODULE->value,
                    $module->id,
                    AuditTrailsActionsEnum::UPDATED->value,
                    $old_module->toArray(),
                    $module->toArray()
                );
            }
        }

        return response()->json([
            'message' => 'Modules updated successfully',
            'modules' => $updatedModules
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modules $modules)
    {
        $this->authorize('delete', $modules);
        $modules->delete();
        AuditTrail::logAction(
            request()->user()->id,
            AuditTrailsEntityTypeEnum::MODULE->value,
            $modules->id,
            AuditTrailsActionsEnum::DELETED->value,
            $modules->toArray(),
            null
        );
        return response()->json([
            'message' => 'Module deleted successfully'
        ]);
    }

    public function massiveDestroy(DestroyMassiveModulesRequest $request)
    {
        $this->authorize('deleteAny', Modules::class);
        $data = $request->validated();
        $deletedCount = Modules::where('project_id', $data['project_id'])->whereIn('id', $data['module_ids'])->delete();
        return response()->json([
            'message' => "Deleted $deletedCount modules for project_id: {$data['project_id']}"
        ], 200);
    }
}
