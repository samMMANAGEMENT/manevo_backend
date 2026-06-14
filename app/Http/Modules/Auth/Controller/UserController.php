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
            'role' => 'required|string|exists:roles,name',
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
     * Get available roles
     */
    public function obtenerRoles()
    {
        // For security, only allow selecting non-superadmin roles for common entities if needed
        // but for now return all active roles
        return response()->json(Role::all());
    }
}
