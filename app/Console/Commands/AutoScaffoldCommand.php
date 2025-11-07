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
            'pdo' => $pdo,
            'dbname'   => config('database.connections.mysql.database'),
            'user'     => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'host'     => config('database.connections.mysql.host'),
            'driver'   => 'pdo_mysql',
        ]);

        $this->schema = $doctrineConnection->createSchemaManager();

        $this->line("─────────────────────────────────────────────");
        $this->info("🚀 Iniciando scaffolding para {$this->modelName} ({$this->tableName})");
        $this->line("─────────────────────────────────────────────");

        if (!Schema::hasTable($this->tableName)) {
            $this->error("❌ La tabla '{$this->tableName}' no existe.");
            return self::FAILURE;
        }

        // 1. Generar Modelo
        if ($this->confirm("¿Desea crear el Modelo {$this->modelName}?", true)) {
            $this->generateModel();
        }

        // 2. Generar Filament Resource con Process y respuesta automática
        if ($this->confirm("¿Desea crear el Filament Resource?", true)) {
            $this->line("⏳ Generando Filament Resource para el panel 'admin'...");

            $process = new Process([
                'php', 'artisan', 'make:filament-resource',
                $this->modelName,
                '--panel=admin',
                '--generate',
                '--force'
            ]);

            $process->setInput("\n"); // simular ENTER en el prompt

            try {
                $process->run();

                if ($process->isSuccessful()) {
                    $this->info("✅ Filament Resource generado con éxito:");
                    $this->line($process->getOutput());
                } else {
                    $this->error("❌ Error al generar el resource:");
                    $this->line($process->getErrorOutput());
                }
            } catch (ProcessFailedException $e) {
                $this->error("💥 Excepción: " . $e->getMessage());
            }
        }

        // 3. Generar ApiController con Spatie QueryBuilder
        if ($this->confirm("¿Desea crear también un ApiController con QueryBuilder?", true)) {
            $controllerName = "{$this->modelName}ApiController";
            $controllerPath = app_path("Http/Controllers/Api/{$controllerName}.php");

            if (!File::exists(app_path('Http/Controllers/Api'))) {
                File::makeDirectory(app_path('Http/Controllers/Api'), 0755, true);
            }

            $stub = <<<EOT
<?php

namespace App\Http\Controllers\Api;

use App\Models\\{$this->modelName};
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
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
            $this->info("📡 ApiController generado con QueryBuilder: App\\Http\\Controllers\\Api\\{$controllerName}");
        }

        // 4. Generar Seeder
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

    private function generateModel(): void
    {
        $columns = Schema::getColumnListing($this->tableName);

        $excluded = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $fillable = array_diff($columns, $excluded);
        $fillableString = '[' . PHP_EOL . '        \'' . implode("',\n        '", $fillable) . '\'' . PHP_EOL . '    ]';

        $castsArray = [];
        foreach ($columns as $col) {
            if ($col === 'id') {
                $castsArray[$col] = 'integer';
            } elseif (str_ends_with($col, '_at')) {
                $castsArray[$col] = 'timestamp';
            } else {
                $castsArray[$col] = 'string';
            }
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

        $useSoftDeletes = in_array('deleted_at', $columns)
            ? 'use Illuminate\\Database\\Eloquent\\SoftDeletes;' : '';
        $softDeletesTrait = in_array('deleted_at', $columns)
            ? 'use SoftDeletes;' : '';

        $relationshipsString = $this->generateRelationships();

        $stub = file_get_contents(base_path('stubs/custom-model.stub'));

        $stub = str_replace(
            [
                '{{ modelNamespace }}',
                '{{ model }}',
                '{{ tableName }}',
                '{{ fillable }}',
                '{{ casts }}',
                '{{ validationRules }}',
                '{{ relationships }}',
                '{{ useSoftDeletes }}',
                '{{ softDeletesTrait }}'
            ],
            [
                'App\\Models',
                $this->modelName,
                $this->tableName,
                $fillableString,
                $castsString,
                $rulesString,
                $relationshipsString,
                $useSoftDeletes,
                $softDeletesTrait
            ],
            $stub
        );

        file_put_contents(app_path("Models/{$this->modelName}.php"), $stub);
        $this->info("✅ Modelo generado en: App\\Models\\{$this->modelName}");
    }

    private function generateRelationships(): string
    {
        $foreignKeys = $this->schema->listTableForeignKeys($this->tableName);
        $relationships = [];

        foreach ($foreignKeys as $foreignKey) {
            $localColumn = $foreignKey->getLocalColumns()[0];
            $foreignTable = $foreignKey->getForeignTableName();
            $foreignColumn = $foreignKey->getForeignColumns()[0];
            $relatedModel = Str::studly(Str::singular($foreignTable));
            $functionName = Str::camel(Str::singular($foreignTable));

            $relationships[] = <<<EOT
    public function {$functionName}()
    {
        return \$this->belongsTo({$relatedModel}::class, '{$localColumn}', '{$foreignColumn}');
    }
EOT;
        }

        $allTables = $this->schema->listTableNames();
        foreach ($allTables as $table) {
            foreach ($this->schema->listTableForeignKeys($table) as $fk) {
                if ($fk->getForeignTableName() === $this->tableName) {
                    $localColumn = $fk->getLocalColumns()[0];
                    $foreignColumn = $fk->getForeignColumns()[0];
                    $relatedModel = Str::studly(Str::singular($table));
                    $functionName = Str::camel(Str::plural($table));

                    $relationships[] = <<<EOT
    public function {$functionName}()
    {
        return \$this->hasMany({$relatedModel}::class, '{$localColumn}', '{$foreignColumn}');
    }
EOT;
                }
            }
        }

        return implode(PHP_EOL . PHP_EOL, $relationships);
    }
}
