<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('funcionario', function (string $value) {
            return User::with('role')->findOrFail($value);
        });

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) use ($frontendUrl) {
            return $frontendUrl.'/reset-password?token='.urlencode($token).'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) use ($frontendUrl) {
            $url = $frontendUrl.'/reset-password?token='.urlencode($token).'&email='.urlencode($notifiable->getEmailForPasswordReset());
            $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Restablecer contraseña - Andrea')
                ->greeting('Hola '.$notifiable->name)
                ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
                ->action('Restablecer contraseña', $url)
                ->line('Este enlace expirará en '.$expire.' minutos.')
                ->line('Si no solicitaste este cambio, puedes ignorar este correo.');
        });
    }
}
