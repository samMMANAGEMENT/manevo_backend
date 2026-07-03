<?php

namespace App\Http\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Modules\Operator\Model\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * List all users of the current entity
     */
    public function obtenerUsuarios()
    {
        $entityId = Auth::user()->entity_id;

        $users = User::where('entity_id', $entityId)
            ->with(['operator', 'roles'])
            ->get();

        return response()->json($users);
    }

    /**
     * Create or Update a user with its operator data
     */
    public function guardarUsuario(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($request->id ?? 'NULL'),
            'password' => $request->id ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|string|exists:roles,name|not_in:super_admin',
            // Operator fields
            'type_document' => 'required|string',
            'document' => 'required|string',
            'mobile' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $entityId = Auth::user()->entity_id;

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'entity_id' => $entityId,
            ];

            if ($request->password) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->id) {
                $user = User::findOrFail($request->id);
                // Security check
                if ($user->entity_id !== $entityId) {
                    abort(403, 'Unauthorized action.');
                }
                $user->update($userData);
            } else {
                // Verificar límite de usuarios según el plan activo de la entidad
                $currentUser = Auth::user();
                $plan = $currentUser->getActivePlan();
                if ($plan && $plan->max_users > 0) {
                    $currentUsersCount = User::where('entity_id', $entityId)->count();
                    if ($currentUsersCount >= $plan->max_users) {
                        return response()->json([
                            'message' => 'Has alcanzado el límite de usuarios permitidos para tu plan (' . $plan->max_users . ' usuarios). Por favor, actualiza tu plan para poder registrar más usuarios.'
                        ], 422);
                    }
                }

                $user = User::create($userData);
            }

            // Sync Role
            $user->syncRoles([$validated['role']]);

            // Save Operator Data
            Operator::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'type_document' => $validated['type_document'],
                    'document' => $validated['document'],
                    'mobile' => $validated['mobile'],
                ]
            );

            return response()->json($user->load(['operator', 'roles']));
        });
    }

    /**
     * Get available roles.
     * super_admin queda excluido: es exclusivo del administrador global de la plataforma
     * y no debe poder asignarse desde la gestión de usuarios de un negocio.
     */
    public function obtenerRoles()
    {
        return response()->json(Role::where('name', '!=', 'super_admin')->get());
    }
}
