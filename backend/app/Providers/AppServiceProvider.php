<?php

namespace App\Providers;

use App\Models\Alumno;
use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\CursoAsignatura;
use App\Models\Establecimiento;
use App\Models\EstadoAsistencia;
use App\Models\Feriado;
use App\Models\Matricula;
use App\Models\PeriodoEscolar;
use App\Models\Persona;
use App\Models\Profesor;
use App\Models\SuspensionClases;
use App\Models\Vacacion;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Resources\Json\JsonResource;
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
        JsonResource::wrap('data');

        Route::bind('periodoEscolar', function (string $value) {
            $establecimiento = Establecimiento::default();

            return PeriodoEscolar::query()
                ->where('establecimiento_id', $establecimiento->id)
                ->raices()
                ->findOrFail($value);
        });

        Route::bind('persona', fn (string $value) => Persona::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('alumno', fn (string $value) => Alumno::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('profesor', fn (string $value) => Profesor::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('curso', fn (string $value) => Curso::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('matricula', fn (string $value) => Matricula::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('asignatura', fn (string $value) => Asignatura::query()->findOrFail($value));

        Route::bind('cursoAsignatura', fn (string $value) => CursoAsignatura::query()->findOrFail($value));

        Route::bind('asignacionDocente', fn (string $value) => AsignacionDocente::query()->findOrFail($value));

        Route::bind('feriado', fn (string $value) => Feriado::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('vacacion', fn (string $value) => Vacacion::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('suspensionClase', fn (string $value) => SuspensionClases::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

        Route::bind('estadoAsistencia', fn (string $value) => EstadoAsistencia::query()->findOrFail($value));

        Route::bind('asistencia', fn (string $value) => Asistencia::query()
            ->where('establecimiento_id', Establecimiento::default()->id)
            ->findOrFail($value));

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
