<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\User;

class LocalSetup extends Command
{
    /** @var string */
    protected $signature = 'local:setup {--test-passwords} {--id=*}';

    /** @var string */
    protected $description = 'Comandos de mantenimiento de la aplicación.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $passwords = $this->option('test-passwords');
        $ids = $this->option('id') ?: [];

        if ($passwords) {
            $this->changeToTestPasswords($ids);
        }
    }

    private function changeToTestPasswords(array $ids): void
    {
        if (!$this->isLocalEnviroment()) {
            $this->info(__("Contraseñas no cambiadas."));
            return;
        }

        $count = count($ids);
        $hasIds = $count > 0;

        $this->info(__("Actualizando contraseña de [:count] para pruebas.", ['count' => $hasIds ? $count : 'todos']));

        $realCount = User::query()
            ->when($hasIds, function ($q) use ($ids) {
                $q->whereIn('id', $ids);
            })
            ->count();

        $password = Hash::make(env('PASSWORD_TESTS', Str::random(14)));

        User::query()
            ->when($hasIds, function ($q) use ($ids) {
                $q->whereIn('id', $ids);
            })
            ->update(['password' => $password]);

        $this->info(__(":count usuarios actualizados.", ['count' => $realCount]));
    }

    private function isLocalEnviroment(): bool
    {
        return in_array(env('APP_ENV'), ['local']);
    }
}
