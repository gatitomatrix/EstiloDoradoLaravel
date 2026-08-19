<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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

    /**
     * Login / registro con Google (Gmail).
     * Cuerpo: { id_token }  o  { access_token }  (GIS / google_sign_in).
     * Demo local: { demo: true } solo si APP_ENV != production.
     */
    public function google(Request $request)
    {
        $data = $request->validate([
            'id_token'     => 'nullable|string',
            'access_token' => 'nullable|string',
            'demo'         => 'nullable|boolean',
            'email'        => 'nullable|email',
            'nombre'       => 'nullable|string|max:100',
            'apellido'     => 'nullable|string|max:100',
        ]);

        $email = null;
        $nombre = 'Cliente';
        $apellido = null;

        if (!empty($data['id_token'])) {
            $payload = $this->verifyGoogleIdToken($data['id_token']);
            if (!$payload || empty($payload['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de Google inválido o expirado',
                ], 401);
            }
            $email = $payload['email'];
            $nombre = $payload['given_name'] ?? ($payload['name'] ?? 'Cliente');
            $apellido = $payload['family_name'] ?? null;
        } elseif (!empty($data['access_token'])) {
            $payload = $this->verifyGoogleAccessToken($data['access_token']);
            if (!$payload || empty($payload['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo leer tu cuenta de Google',
                ], 401);
            }
            $email = $payload['email'];
            $nombre = $payload['given_name'] ?? ($payload['name'] ?? 'Cliente');
            $apellido = $payload['family_name'] ?? null;
        } elseif (!empty($data['demo']) && !app()->environment('production')) {
            $email = $data['email'] ?? 'demo.google@estilodorado.local';
            $nombre = $data['nombre'] ?? 'Cliente';
            $apellido = $data['apellido'] ?? 'Google Demo';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Elige tu Gmail. Si falló, revisa GOOGLE_CLIENT_ID en Laravel.',
            ], 422);
        }

        $cliente = Cliente::where('email', $email)->first();
        $created = false;
        if (!$cliente) {
            $cliente = Cliente::create([
                'nombre'     => $nombre,
                'apellido'   => $apellido,
                'email'      => $email,
                'telefono'   => null,
                'direccion'  => null,
                'contrasena' => Hash::make(Str::random(32)),
            ]);
            $created = true;
        }

        $token = $cliente->createToken('token_cliente', ['client'])->plainTextToken;

        return response()->json([
            'success' => true,
            'created' => $created,
            'message' => $created ? 'Cuenta creada con Google' : 'Login con Google exitoso',
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

    private function googleHttp()
    {
        $http = Http::timeout(10)->acceptJson();
        $verify = filter_var(env('VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN);
        if (!$verify || app()->environment('local')) {
            $http = $http->withoutVerifying();
        }

        return $http;
    }

    /** @return array<string, mixed>|null */
    private function verifyGoogleIdToken(string $idToken): ?array
    {
        try {
            $res = $this->googleHttp()->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);
            if (!$res->ok()) {
                return null;
            }
            $payload = $res->json();
            if (!is_array($payload) || empty($payload['email'])) {
                return null;
            }
            $expected = config('services.google.client_id');
            $aud = $payload['aud'] ?? null;
            if ($expected && $aud && $aud !== $expected) {
                Log::warning('[google] aud mismatch', ['aud' => $aud]);

                return null;
            }

            return $payload;
        } catch (\Throwable $e) {
            Log::warning('[google] id_token fail: '.$e->getMessage());

            return null;
        }
    }

    /** @return array<string, mixed>|null */
    private function verifyGoogleAccessToken(string $accessToken): ?array
    {
        try {
            $res = $this->googleHttp()
                ->withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if (!$res->ok()) {
                return null;
            }
            $payload = $res->json();
            if (!is_array($payload) || empty($payload['email'])) {
                return null;
            }

            return $payload;
        } catch (\Throwable $e) {
            Log::warning('[google] access_token fail: '.$e->getMessage());

            return null;
        }
    }
}
