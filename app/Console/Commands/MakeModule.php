<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {module : El nombre del modulo (ej: User, Auth, Billing)} 
                                        {name : El nombre del componente (ej: Profile, Invoice)} 
                                        {--m|migration : Crear también el archivo de migración}';

    /**
     * El nombre descriptivo del comando.
     *
     * @var string
     */
    protected $description = 'Crea Model, Controller y Service en una estructura modular personalizada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $createMigration = $this->option('migration');

        $this->createModel($module, $name);
        $this->createController($module, $name);
        $this->createService($module, $name);

        if ($createMigration) {
            $tableName = Str::snake(Str::pluralStudly($name));
            $this->call('make:migration', [
                'name' => "create_{$tableName}_table",
                '--create' => $tableName
            ]);
        }

        $this->newLine();
        $this->info("🚀 Modulo [{$module}] para componente [{$name}] creado correctamente.");
    }

    protected function createModel($module, $name)
    {
        $path = app_path("Http/Modules/{$module}/Model/{$name}.php");
        $namespace = "App\\Http\\Modules\\{$module}\\Model";

        $content = "<?php\n\nnamespace {$namespace};\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$name} extends Model\n{\n    //\n}\n";

        $this->writeFile($path, $content, 'Model');
    }

    protected function createController($module, $name)
    {
        $path = app_path("Http/Modules/{$module}/Controller/{$name}Controller.php");
        $namespace = "App\\Http\\Modules\\{$module}\\Controller";

        $content = "<?php\n\nnamespace {$namespace};\n\nuse App\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\n\nclass {$name}Controller extends Controller\n{\n    //\n}\n";

        $this->writeFile($path, $content, 'Controller');
    }

    protected function createService($module, $name)
    {
        $path = app_path("Http/Modules/{$module}/Services/{$name}Service.php");
        $namespace = "App\\Http\\Modules\\{$module}\\Services";

        $content = "<?php\n\nnamespace {$namespace};\n\nclass {$name}Service\n{\n    //\n}\n";

        $this->writeFile($path, $content, 'Service');
    }

    protected function writeFile($path, $content, $type)
    {
        $directory = dirname($path);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($path)) {
            $this->error("FAILED: {$type} ya existe en {$path}");
            return;
        }

        File::put($path, $content);
        $this->line("<info>INFO</info> {$type} creado exitosamente en: <comment>{$path}</comment>");
    }
}
