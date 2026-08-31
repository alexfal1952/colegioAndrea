<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class FuncionarioController extends Controller
{
    public function index(): JsonResponse
    {
        $funcionarios = User::with('role')
            ->orderBy('name')
            ->get();

        return response()->json($funcionarios);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'role_id.required' => 'Debes asignar un perfil.',
            'role_id.exists' => 'El perfil seleccionado no es válido.',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Str::password(16),
        ]);

        $reset = $this->dispatchPasswordReset($user);

        $message = $reset['mail_sent']
            ? 'Funcionario creado. Se envió un correo para que defina su contraseña.'
            : 'Funcionario creado, pero el correo no pudo entregarse. Usa el enlace de restablecimiento.';

        return response()->json([
            'funcionario' => $user->load('role'),
            'message' => $message,
            'mail_sent' => $reset['mail_sent'],
            'reset_url' => $reset['reset_url'],
            'mail_error' => $reset['mail_error'],
        ], 201);
    }

    public function update(Request $request, User $funcionario): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($funcionario->id),
            ],
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'role_id.required' => 'Debes asignar un perfil.',
            'role_id.exists' => 'El perfil seleccionado no es válido.',
        ]);

        $funcionario->update($validated);

        return response()->json($funcionario->fresh()->load('role'));
    }

    public function destroy(Request $request, User $funcionario): JsonResponse
    {
        if ($request->user()->id === $funcionario->id) {
            throw ValidationException::withMessages([
                'funcionario' => ['No puedes eliminar tu propia cuenta.'],
            ]);
        }

        $funcionario->tokens()->delete();
        $funcionario->delete();

        return response()->json([
            'message' => 'Funcionario eliminado correctamente.',
        ]);
    }

    public function sendPasswordReset(User $funcionario): JsonResponse
    {
        $reset = $this->dispatchPasswordReset($funcionario);

        if (! $reset['mail_sent'] && ! $reset['reset_url']) {
            throw ValidationException::withMessages([
                'email' => [$reset['mail_error'] ?? 'No se pudo generar el enlace de restablecimiento.'],
            ]);
        }

        $message = $reset['mail_sent']
            ? "Se envió un enlace de restablecimiento a {$funcionario->email}."
            : "No se pudo entregar el correo a {$funcionario->email}. Copia el enlace y compártelo manualmente.";

        return response()->json([
            'message' => $message,
            'mail_sent' => $reset['mail_sent'],
            'reset_url' => $reset['reset_url'],
            'mail_error' => $reset['mail_error'],
        ]);
    }

    public function roles(): JsonResponse
    {
        return response()->json(Role::orderBy('name')->get());
    }

    /**
     * @return array{mail_sent: bool, reset_url: string|null, mail_error: string|null}
     */
    private function dispatchPasswordReset(User $user): array
    {
        $token = Password::broker()->createToken($user);
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $resetUrl = $frontendUrl.'/reset-password?token='.urlencode($token).'&email='.urlencode($user->email);
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        $mailSent = false;
        $mailError = null;

        try {
            // Mismo canal SMTP de la prueba que sí llegó (Mail::raw / Mailable simple)
            Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl, $expire));
            $mailSent = true;
        } catch (Throwable $e) {
            $mailSent = false;
            $mailError = $e->getMessage();
            Log::error('Fallo al enviar correo de restablecimiento', [
                'email' => $user->email,
                'error' => $mailError,
            ]);
        }

        Log::info('Enlace de restablecimiento generado', [
            'email' => $user->email,
            'mail_sent' => $mailSent,
            'reset_url' => $resetUrl,
        ]);

        return [
            'mail_sent' => $mailSent,
            'reset_url' => $resetUrl,
            'mail_error' => $mailError,
        ];
    }
}