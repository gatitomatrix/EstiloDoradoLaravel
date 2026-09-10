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
use App\Mail\PasswordChangedMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthClienteController extends Controller
{
    // Login tienda: correo+clave o Google. Al registrarse manda WelcomeMail (Dori + logo).
    // Google no usa nuestra clave: si pide "olvidé contraseña", lo mando a su cuenta Google.
    private const MSG_GOOGLE = 'Esta cuenta entra con Google. Usa el botón «Continuar con Google». La contraseña se cambia en tu cuenta de Google.';

    /**
     * Registro de nuevo cliente
     */
    public function register(Request $request)
    {
        if (!$request->filled('password') && $request->filled('contrasena')) {
            $request->merge(['password' => $request->input('contrasena')]);
        }
        if (!$request->filled('password_confirmation')) {
            $request->merge([
                'password_confirmation' => $request->input('password')
                    ?? $request->input('contrasena')
                    ?? $request->input('contrasena_confirmation'),
            ]);
        }

        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'apellido'  => 'nullable|string|max:100',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'required|email|max:100|unique:clientes,email',
            'direccion' => 'nullable|string|max:255',
            'password'  => 'required|string|min:6',
        ], [
            'email.unique' => 'Este correo ya está registrado. Inicia sesión o recupera tu contraseña.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $dir = trim((string) ($data['direccion'] ?? ''));
        $payload = [
            'nombre'    => $data['nombre'],
            'apellido'  => $data['apellido'] ?? null,
            'telefono'  => $data['telefono'] ?? null,
            'email'     => $data['email'],
            'direccion' => $dir === '' ? null : $dir,
            'contrasena'=> Hash::make($data['password']),
        ];
        if (Schema::hasColumn('clientes', 'auth_provider')) {
            $payload['auth_provider'] = Cliente::PROVIDER_LOCAL;
        }

        try {
            $cliente = Cliente::create($payload);
            $token = $cliente->createToken('token_cliente', ['client'])->plainTextToken;
        } catch (\Throwable $e) {
            Log::error('[register] '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear la cuenta. Intenta de nuevo o usa otro correo.',
            ], 500);
        }

        try {
            Mail::to($cliente->email)->send(new WelcomeMail($cliente));
        } catch (\Throwable $e) {
            Log::warning('[register] WelcomeMail falló: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'cliente' => $cliente->toAuthArray(),
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

        if ($cliente->esGoogle()) {
            return response()->json([
                'success' => false,
                'message' => self::MSG_GOOGLE,
                'auth_provider' => Cliente::PROVIDER_GOOGLE,
            ], 401);
        }

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
            'cliente' => $cliente->toAuthArray(),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user || !($user instanceof Cliente)) {
            return response()->json(['message' => 'Token no válido para CLIENTE'], 401);
        }

        return response()->json($user->toAuthArray());
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

        return response()->json($c->toAuthArray(), 200);
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

    /**
     * Envía un código de 6 dígitos (60 min). Siempre 200 para no filtrar si el correo existe.
     */
    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($data['email']));

        $cliente = Cliente::where('email', $email)->first();
        if ($cliente && $cliente->esGoogle()) {
            return response()->json([
                'success' => true,
                'google' => true,
                'message' => self::MSG_GOOGLE,
            ]);
        }
        if ($cliente) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            $resetUrl = config('app.frontend_url').'/restablecer?email='.urlencode($email).'&codigo='.$code;

            try {
                Mail::to($cliente->email)->send(new ResetPasswordMail($cliente, $code, $resetUrl));
            } catch (\Throwable $e) {
                Log::warning('[forgotPassword] mail: '.$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Si el correo está registrado, te enviamos un código para restablecer la contraseña.',
        ]);
    }

    /**
     * Restablece con el código del correo.
     */
    public function resetWithCode(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string|min:4|max:12',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = strtolower(trim($data['email']));
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (! $row) {
            return response()->json(['message' => 'Código inválido o vencido. Pide uno nuevo.'], 422);
        }

        $created = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null;
        if (! $created || $created->lt(now()->subMinutes(60))) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json(['message' => 'El código venció. Pide uno nuevo.'], 422);
        }

        if (! Hash::check(trim($data['codigo']), $row->token)) {
            return response()->json(['message' => 'Código inválido.'], 422);
        }

        $cliente = Cliente::where('email', $email)->first();
        if (! $cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
        if ($cliente->esGoogle()) {
            return response()->json(['message' => self::MSG_GOOGLE], 422);
        }

        $cliente->contrasena = Hash::make($data['password']);
        $cliente->save();
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        try {
            Mail::to($cliente->email)->send(new PasswordChangedMail($cliente));
        } catch (\Throwable $e) {
            Log::warning('[resetWithCode] mail: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada'], 200);
    }

    /**
     * @deprecated Usar forgot + reset con código. Se deja para no romper clientes viejos,
     * pero exige el código del correo (ya no cambia la clave solo con el email).
     */
    public function resetSimple(Request $request)
    {
        if (! $request->filled('password') && $request->filled('contrasena')) {
            $request->merge([
                'password' => $request->input('contrasena'),
                'password_confirmation' => $request->input('password_confirmation')
                    ?? $request->input('contrasena_confirmation')
                    ?? $request->input('contrasena'),
            ]);
        }
        if (! $request->filled('codigo') && $request->filled('code')) {
            $request->merge(['codigo' => $request->input('code')]);
        }

        return $this->resetWithCode($request);
    }

    public function updatePassword(Request $request)
    {
        $c = $request->user();
        if (!$c || !($c instanceof Cliente)) {
            return response()->json(['message' => 'Token no válido para CLIENTE'], 401);
        }
        if ($c->esGoogle()) {
            return response()->json(['message' => self::MSG_GOOGLE], 422);
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

        try {
            Mail::to($c->email)->send(new PasswordChangedMail($c));
        } catch (\Throwable $e) {
            Log::warning('[updatePassword] mail: '.$e->getMessage());
        }

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
            $nuevo = [
                'nombre'     => $nombre,
                'apellido'   => $apellido,
                'email'      => $email,
                'telefono'   => null,
                'direccion'  => null,
                'contrasena' => Hash::make(Str::random(32)),
            ];
            if (Schema::hasColumn('clientes', 'auth_provider')) {
                $nuevo['auth_provider'] = Cliente::PROVIDER_GOOGLE;
            }
            $cliente = Cliente::create($nuevo);
            $created = true;
        } elseif (Schema::hasColumn('clientes', 'auth_provider')
            && $cliente->auth_provider !== Cliente::PROVIDER_GOOGLE) {
            $cliente->auth_provider = Cliente::PROVIDER_GOOGLE;
            $cliente->save();
        }

        $token = $cliente->createToken('token_cliente', ['client'])->plainTextToken;

        if ($created) {
            try {
                Mail::to($cliente->email)->send(new WelcomeMail($cliente));
            } catch (\Throwable $e) {
                Log::warning('[google] WelcomeMail: '.$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'message' => $created ? 'Cuenta creada con Google' : 'Login con Google exitoso',
            'cliente' => $cliente->toAuthArray(),
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
