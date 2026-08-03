<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeMail;

class AuthClienteController extends Controller
{
    /**
     * Registro de nuevo cliente
     */
    public function register(Request $request)
    {
        // Acepta password (móvil/API estándar) o contrasena (Angular)
        if (!$request->filled('password') && $request->filled('contrasena')) {
            $request->merge([
                'password' => $request->input('contrasena'),
                'password_confirmation' => $request->input('password_confirmation')
                    ?? $request->input('contrasena_confirmation')
                    ?? $request->input('contrasena'),
            ]);
        }

        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'apellido'  => 'nullable|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'required|email|max:100|unique:clientes,email',
            'direccion' => 'nullable|string',
            'password'  => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Este correo ya está registrado. Inicia sesión o recupera tu contraseña.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $cliente = Cliente::create([
            'nombre'    => $data['nombre'],
            'apellido'  => $data['apellido'] ?? null,
            'telefono'  => $data['telefono'] ?? null,
            'email'     => $data['email'],
            'direccion' => $data['direccion'] ?? null,
            'contrasena'=> Hash::make($data['password']),
        ]);

        // Ability "client" para middleware auth:sanctum,abilities:client
        $token = $cliente->createToken('token_cliente', ['client'])->plainTextToken;

        // Correo de bienvenida: no debe tumbar el registro si falla SMTP
        try {
            Mail::to($cliente->email)->send(new WelcomeMail($cliente));
        } catch (\Throwable $e) {
            Log::warning('[register] WelcomeMail falló: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'cliente' => [
                'id_cliente' => $cliente->id_cliente,
                'nombre'     => $cliente->nombre,
                'apellido'   => $cliente->apellido,
                'telefono'   => $cliente->telefono,
                'email'      => $cliente->email,
                'direccion'  => $cliente->direccion,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Login de cliente
     */
    public function login(Request $request)
    {
        if (!$request->filled('password') && $request->filled('contrasena')) {
            $request->merge(['password' => $request->input('contrasena')]);
        }

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $cliente = Cliente::where('email', $credentials['email'])->first();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $stored = $cliente->getAuthPassword();
        $looksBcrypt = is_string($stored) && Str::startsWith($stored, '$2y$');

        if ($looksBcrypt) {
            if (!Hash::check($credentials['password'], $stored)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }
        } else {
            if ($credentials['password'] !== $stored) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }
            $cliente->contrasena = Hash::make($credentials['password']);
            $cliente->save();
        }

        $token = $cliente->createToken('token_cliente', ['client'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'cliente' => [
                'id_cliente' => $cliente->id_cliente,
                'nombre'     => $cliente->nombre,
                'apellido'   => $cliente->apellido,
                'telefono'   => $cliente->telefono,
                'email'      => $cliente->email,
                'direccion'  => $cliente->direccion,
            ],
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user || !($user instanceof Cliente)) {
            return response()->json(['message' => 'Token no válido para CLIENTE'], 401);
        }

        return response()->json([
            'id_cliente' => $user->id_cliente,
            'nombre'     => $user->nombre,
            'apellido'   => $user->apellido,
            'telefono'   => $user->telefono,
            'direccion'  => $user->direccion,
            'email'      => $user->email,
        ]);
    }

    public function update(Request $request)
    {
        $c = $request->user();
        if (!$c || !($c instanceof Cliente)) {
            return response()->json(['message' => 'Token no válido para CLIENTE'], 401);
        }

        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'apellido'  => 'nullable|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
        ]);

        $c->fill($data)->save();

        return response()->json([
            'id_cliente' => $c->id_cliente,
            'nombre'     => $c->nombre,
            'apellido'   => $c->apellido,
            'telefono'   => $c->telefono,
            'direccion'  => $c->direccion,
            'email'      => $c->email,
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function checkEmail(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $exists = Cliente::where('email', $data['email'])->exists();
        return $exists
            ? response()->json(['exists' => true], 200)
            : response()->json(['exists' => false], 404);
    }

    public function resetSimple(Request $request)
    {
        $data = $request->validate([
            'email'      => 'required|email',
            'contrasena' => 'required|string|min:6|max:255',
        ]);

        $cliente = Cliente::where('email', $data['email'])->first();
        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->contrasena = Hash::make($data['contrasena']);
        $cliente->save();

        return response()->json(['message' => 'Contraseña actualizada'], 200);
    }

    public function updatePassword(Request $request)
    {
        $c = $request->user();
        if (!$c || !($c instanceof Cliente)) {
            return response()->json(['message' => 'Token no válido para CLIENTE'], 401);
        }

        $data = $request->validate([
            'password_actual' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $stored = $c->getAuthPassword();
        if (!Hash::check($data['password_actual'], $stored) && $data['password_actual'] !== $stored) {
            return response()->json(['message' => 'La contraseña actual no es correcta'], 422);
        }

        $c->contrasena = Hash::make($data['password']);
        $c->save();

        return response()->json(['message' => 'Contraseña actualizada'], 200);
    }
}
