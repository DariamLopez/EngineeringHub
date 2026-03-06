<?php

namespace App\Http\Controllers;

use App\Enums\AgencyPermission;
use App\Enums\BookingPermission;
use App\Enums\BrandPermission;
use App\Enums\CustomerPermission;
use App\Enums\PaymentPermission;
use App\Enums\UserPermission;
use App\Enums\RoleName;
use App\Enums\ShipPermission;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /*
     * $request: name, email, password, role
     */
    public function register(RegisterRequest $request)
    {
        $data = $request;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Asignar rol por defecto usando enum
        $user->assignRole($request->input('roles'));

        // Opcional: crear token con abilities basadas en rol
        $abilities = $this->abilitiesForRole($request->input('roles'));
        $token = $user->createToken('api-token', $abilities)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'abilities' => $abilities
        ], 201);
    }

    public function login(LoginRequest $request)
    {

        $credentials = $request->all();

        $user = User::where('email', $credentials['email'])->with('roles', 'permissions')->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        /* // Opcional: bloqueo por estado, email verified, etc.
        if (method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email no verificado.'], 403);
        } */

        // Revocar tokens antiguos si se solicita (rotación opcional)
        if (! empty($credentials['revoke_old_tokens'])) {
            $user->tokens()->delete();
        }

        // Determinar abilities según roles/permissions
        $abilities = $this->abilitiesForUser($user);

        $token = $user->createToken('api-token', $abilities)->plainTextToken;
        $tokenModel = $user->tokens()->where('token', hash('sha256', explode('|', $token, 2)[1]))->first();
        $tokenModel->expires_at = now()->addHours(1); // Expiración a 1 horas
        $tokenModel->save();

        Log::info('User logged in', ['user_id' => $user, 'abilities' => $abilities]);
        return response()->json([
            'user' => $user,
            'token' => $token,
            'abilities' => $abilities
        ]);
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    protected function abilitiesForUser(User $user): array
    {
        // Si tiene rol admin, dar todas las abilities
        if ($user->hasRole('admin')) {
            return ['*'];
        }

        // Recolectar permisos directos y por rol
        $perms = $user->getAllPermissions()->pluck('name')->unique()->toArray();

        // Si no hay permisos explícitos, asignar por rol por defecto
        /* if (empty($perms)) {
            foreach ($user->getRoleNames() as $roleName) {
                $roleEnum = collect(RoleName::cases())->first(fn($r) => $r->value === $roleName);
                if ($roleEnum) {
                    $perms = array_merge($perms, $this->abilitiesForRole($roleEnum));
                }
            }
        } */

        return array_values(array_unique($perms));
    }
    protected function abilitiesForRole($role): array
    {
        $role = Role::findByName($role);
        return $role->permissions->pluck('name')->toArray();
    }
}

