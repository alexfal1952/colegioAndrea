<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Respuesta genérica por seguridad (no revelar si el email existe)
        $genericMessage = 'Si el correo existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.';

        if (! $user) {
            return response()->json(['message' => $genericMessage]);
        }

        $token = Password::broker()->createToken($user);
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $resetUrl = $frontendUrl.'/reset-password?token='.urlencode($token).'&email='.urlencode($user->email);
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl, $expire));
        } catch (Throwable $e) {
            Log::error('Fallo al enviar forgot-password', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => $genericMessage]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__('passwords.token')],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Contraseña restablecida correctamente.',
            'user' => $user->load('role'),
            'token' => $token,
        ]);
    }
}
