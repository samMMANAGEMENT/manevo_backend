<?php

namespace App\Http\Modules\Entity\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Entity\Services\EntityService;

class EntityController extends Controller
{
    public function __construct(private EntityService $entityService)
    {
    }

    public function miEntidad()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'No autorizado'], 401);
            }

            if (!$user->entity_id) {
                return response()->json(['message' => 'El usuario no tiene una entidad asociada'], 404);
            }

            $entity = $this->entityService->obtenerEntidad($user->entity_id);
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerEntidades()
    {
        try {
            $entities = $this->entityService->obtenerEntidades();
            return response()->json($entities, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function crearEntidad(Request $request)
    {
        try {
            $entity = $this->entityService->crearEntidad($request->all());
            return response()->json($entity, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerEntidad($id)
    {
        try {
            $entity = $this->entityService->obtenerEntidad($id);
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function modificarEntidad(Request $request, $id)
    {
        try {
            $entity = $this->entityService->modificarEntidad($request->all(), $id);
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarEntidad($id)
    {
        try {
            $this->entityService->eliminarEntidad($id);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function asignarPlan(Request $request, $id)
    {
        try {
            $entity = $this->entityService->asignarPlan($id, $request->get('plan_id'), $request->all());
            return response()->json($entity, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function cambiarEntidad(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'No autorizado'], 401);
            }

            if (!$user->can('change.entity') && !$user->hasRole('super_admin')) {
                return response()->json(['message' => 'No tienes permiso para cambiar de entidad'], 403);
            }

            $request->validate([
                'entity_id' => 'required|exists:entities,id'
            ]);

            $entityId = $request->get('entity_id');
            $user->entity_id = $entityId;
            $user->save();

            // Load and format permissions for frontend compatibility
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
            $roles = $user->roles->pluck('name')->toArray();

            return response()->json([
                'message' => 'Entidad cambiada con éxito',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'entity_id' => $user->entity_id,
                    'roles' => $roles,
                    'permissions' => $permissions
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
