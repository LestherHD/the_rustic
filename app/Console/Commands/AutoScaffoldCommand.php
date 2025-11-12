<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Doctrine\DBAL\DriverManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AutoScaffoldCommand extends Command
{
    protected $signature = 'scaffolding:auto {model} {table}';
    protected $description = 'Genera Modelo, Filament Resource, ApiController y Seeder basados en la tabla y modelo especificados';

    private $schema;
    private $tableName;
    private $modelName;

    public function handle(): int
    {
        $this->tableName = $this->argument('table');
        $this->modelName = Str::studly($this->argument('model'));
        $pdo = DB::connection()->getPdo();

        $doctrineConnection = DriverManager::getConnection([
            'pdo'     => $pdo,
            'dbname'  => config('database.connections.mysql.database'),
            'user'    => config('database.connections.mysql.username'),
            'password'=> config('database.connections.mysql.password'),
            'host'    => config('database.connections.mysql.host'),
            'driver'  => 'pdo_mysql',
        ]);

        $this->schema = $doctrineConnection->createSchemaManager();

        $this->line("─────────────────────────────────────────────");
        $this->info("🚀 Iniciando scaffolding para {$this->modelName} ({$this->tableName})");
        $this->line("─────────────────────────────────────────────");

        if (! Schema::hasTable($this->tableName)) {
            $this->error("❌ La tabla '{$this->tableName}' no existe.");
            return self::FAILURE;
        }

        // 1️⃣ Generar Modelo
        if ($this->confirm("¿Desea crear el Modelo {$this->modelName}?", true)) {
            $this->generateModel();
        }

        // 2️⃣ Generar Filament Resource
        if ($this->confirm("¿Desea crear el Filament Resource?", true)) {
            $this->line("⏳ Generando Filament Resource...");

            $process = new Process([
                'php', 'artisan', 'make:filament-resource',
                $this->modelName,
                '--panel=admin',
                '--generate',
                '--force'
            ]);
            $process->setInput("\n");

            try {
                $process->run();

                if ($process->isSuccessful()) {
                    $this->info("✅ Filament Resource generado con éxito:");
                    $this->line($process->getOutput());

                    // ✅ Reubicar correctamente
                    $resourceFolder = base_path("app/Filament/Admin/Resources/{$this->modelName}s");
                    $resourceFile = "{$resourceFolder}/{$this->modelName}Resource.php";
                    $targetFile = base_path("app/Filament/Admin/Resources/{$this->modelName}Resource.php");

                    if (File::exists($resourceFile)) {
                        File::move($resourceFile, $targetFile);
                        $this->info("📁 Movido correctamente a: {$targetFile}");
                    }

                    // ✅ Insertar imports y propiedades
                    if (File::exists($targetFile)) {
                        $this->insertNavigationProperties($targetFile);
                    }

                    // ✅ Agregar columna de acciones
                    $tableFilePath = base_path("app/Filament/Admin/Resources/{$this->modelName}Resource/Tables/{$this->modelName}Table.php");
                    if (File::exists($tableFilePath)) {
                        $contents = File::get($tableFilePath);
                        if (! Str::contains($contents, 'ActionGroup::make')) {
                            $pattern     = '/return\s+\[(.*?)\];/s';
                            $replacement = <<<PHP
return [
    Tables\Actions\ActionGroup::make([
        Tables\Actions\ViewAction::make()->label('Ver'),
        Tables\Actions\EditAction::make()->label('Editar'),
        Tables\Actions\DeleteAction::make()->label('Eliminar'),
    ]),
];
PHP;
                            $modified = preg_replace($pattern, $replacement, $contents);
                            File::put($tableFilePath, $modified);
                            $this->info("🛠️ Columna de acciones agregada en: {$tableFilePath}");
                        }
                    }
                } else {
                    $this->error("❌ Error al generar el resource:");
                    $this->line($process->getErrorOutput());
                }
            } catch (ProcessFailedException $e) {
                $this->error("💥 Excepción: " . $e->getMessage());
            }
        }

        // 3️⃣ Generar ApiController
        if ($this->confirm("¿Desea crear también un ApiController con QueryBuilder?", true)) {
            $controllerName = "{$this->modelName}ApiController";
            $controllerPath = app_path("Http/Controllers/Api/{$controllerName}.php");

            if (! File::exists(app_path('Http/Controllers/Api'))) {
                File::makeDirectory(app_path('Http/Controllers/Api'), 0755, true);
            }

            $stub = <<<EOT
<?php

namespace App\Http\Controllers\Api;

use App\Models\\{$this->modelName};
use Illuminate\Http\Request;
use Spatie\\QueryBuilder\\QueryBuilder;
use App\Http\Controllers\Controller;

class {$controllerName} extends Controller
{
    public function index(Request \$request)
    {
        \$items = QueryBuilder::for({$this->modelName}::class)
            ->allowedFilters(['nombre'])
            ->allowedSorts(['id', 'created_at'])
            ->paginate();

        return response()->json(\$items);
    }

    public function store(Request \$request)
    {
        \$data = \$request->validate({$this->modelName}::\$rules);
        \$item = {$this->modelName}::create(\$data);

        return response()->json(\$item, 201);
    }

    public function show(\$id)
    {
        \$item = {$this->modelName}::findOrFail(\$id);
        return response()->json(\$item);
    }

    public function update(Request \$request, \$id)
    {
        \$item = {$this->modelName}::findOrFail(\$id);
        \$data = \$request->validate({$this->modelName}::\$rules);
        \$item->update(\$data);

        return response()->json(\$item);
    }

    public function destroy(\$id)
    {
        \$item = {$this->modelName}::findOrFail(\$id);
        \$item->delete();

        return response()->json(null, 204);
    }
}
EOT;

            File::put($controllerPath, $stub);
            $this->info("📡 ApiController generado: App\\Http\\Controllers\\Api\\{$controllerName}");
        }

        // 4️⃣ Generar Seeder
        if ($this->confirm("¿Desea crear un Seeder para {$this->modelName}?", true)) {
            Artisan::call("make:seeder", [
                "name" => "{$this->modelName}Seeder"
            ]);
            $this->info("🌱 Seeder generado: {$this->modelName}Seeder");
        }

        $this->line("\n─────────────────────────────────────────────");
        $this->info("✅ Proceso finalizado con éxito");
        $this->line("─────────────────────────────────────────────");

        return self::SUCCESS;
    }

    private function insertNavigationProperties(string $filePath): void
    {
        $contents = File::get($filePath);

        // ✅ Evitar duplicados
        if (Str::contains($contents, '$navigationGroup')) {
            $this->warn("⚠️ Este resource ya contiene propiedades de navegación. No se insertaron nuevamente.");
            return;
        }

        // ✅ Agregar import UnitEnum si no existe
        if (!Str::contains($contents, 'use UnitEnum;')) {
            $contents = preg_replace('/(use\s+BackedEnum;)/', "$1\nuse UnitEnum;", $contents, 1);
        }

        // ✅ Insertar propiedades dentro de la clase
        $pattern = '/(protected\s+static\s+\?string\s+\$model\s+=\s+[^;]+;)/';
        $insertion = <<<PHP

    // 🔹 Propiedades de navegación Filament
    protected static BackedEnum|string|null \$navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static UnitEnum|string|null \$navigationGroup = 'Gestión del Sistema';
    protected static ?string \$navigationLabel = '{$this->modelName}s';
PHP;

        $newContents = preg_replace($pattern, "$1\n$insertion", $contents, 1);

        File::put($filePath, $newContents);
        $this->info("🎨 Propiedades de navegación agregadas correctamente en: {$filePath}");
    }

    private function generateModel(): void
    {
        $columns = Schema::getColumnListing($this->tableName);
        $excluded = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $fillable = array_diff($columns, $excluded);
        $fillableString = '[' . PHP_EOL . '        \'' . implode("',\n        '", $fillable) . '\'' . PHP_EOL . '    ]';

        $castsArray = [];
        foreach ($columns as $col) {
            $castsArray[$col] = str_ends_with($col, '_at') ? 'datetime' : 'string';
        }
        $castsString = '[' . PHP_EOL;
        foreach ($castsArray as $field => $type) {
            $castsString .= "        '$field' => '$type'," . PHP_EOL;
        }
        $castsString .= '    ]';

        $rulesArray = [];
        foreach ($fillable as $col) {
            $rulesArray[$col] = 'required';
        }
        $rulesString = '[' . PHP_EOL;
        foreach ($rulesArray as $field => $rule) {
            $rulesString .= "        '$field' => '$rule'," . PHP_EOL;
        }
        $rulesString .= '    ]';

        $stub = file_get_contents(base_path('stubs/custom-model.stub'));
        $stub = str_replace(
            ['{{ modelNamespace }}', '{{ model }}', '{{ tableName }}', '{{ fillable }}', '{{ casts }}', '{{ validationRules }}', '{{ relationships }}', '{{ useSoftDeletes }}', '{{ softDeletesTrait }}'],
            ['App\\Models', $this->modelName, $this->tableName, $fillableString, $castsString, $rulesString, '', '', ''],
            $stub
        );

        file_put_contents(app_path("Models/{$this->modelName}.php"), $stub);
        $this->info("✅ Modelo generado en: App\\Models\\{$this->modelName}");
    }
}
