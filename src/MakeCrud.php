<?php

namespace Jasrys\Crud;

use Illuminate\Console\Command;

class MakeCrud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {model} {--attributes=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make files for CRUD operations on a model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $modelName = $this->argument('model');

        $attributes = collect(explode(',', $this->option('attributes')));

        $this->createModel($modelName, $attributes);

        $this->createController($modelName);

        $this->createFeatureTests($modelName);

        $this->createModelFactory($modelName, $attributes);

        $this->createMigration($modelName, $attributes);

        $this->createViews($modelName, $attributes);

        $this->createResourceRoutes($modelName);

        $this->call('migrate');
    }

    protected function createModel($modelName, $attributes)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/model.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        $formatedAttributes = $attributes->map(function ($attribute) {
            return "'" . $attribute . "'";
        });

        $stub = str_replace('ATTRIBUTES', $formatedAttributes->implode(',' . PHP_EOL . "\t\t"), $stub);

        file_put_contents(app_path($modelName . '.php'), $stub);
    }

    protected function createController($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/controller.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(app_path('Http/Controllers/' . $modelName . 'Controller.php'), $stub);
    }

    protected function createFeatureTests($modelName)
    {
        $this->createDirectory(base_path('tests/Feature/' . str_plural($modelName)));

        $this->createViewingModelTest($modelName);

        $this->createCreatingModelTest($modelName);

        $this->createEditingModelTest($modelName);

        $this->createDeletingModelTest($modelName);
    }

    protected function createViewingModelTest($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/viewing_test.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(
            base_path('tests/Feature/' . str_plural(strtolower($modelName)) . '/Viewing' . str_plural($modelName) . 'Test.php'),
            $stub
        );
    }

    protected function createCreatingModelTest($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/creating_test.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(
            base_path('tests/Feature/' . str_plural(strtolower($modelName)) . '/Creating' . str_plural($modelName) . 'Test.php'),
            $stub
        );
    }

    protected function createEditingModelTest($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/editing_test.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(
            base_path('tests/Feature/' . str_plural(strtolower($modelName)) . '/Editing' . str_plural($modelName) . 'Test.php'),
            $stub
        );
    }

    protected function createDeletingModelTest($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/deleting_test.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(
            base_path('tests/Feature/' . str_plural(strtolower($modelName)) . '/Deleting' . str_plural($modelName) . 'Test.php'),
            $stub
        );
    }

    protected function replacePlaceholdersWithModelName($stub, $modelName)
    {
        $stub = str_replace('MODEL_NAME_PLURAL_LOWERCASE', strtolower(str_plural($modelName)), $stub);

        $stub = str_replace('MODEL_NAME_PLURAL', str_plural($modelName), $stub);

        $stub = str_replace('MODEL_NAME_LOWERCASE', strtolower($modelName), $stub);

        $stub = str_replace('MODEL_NAME', $modelName, $stub);

        return $stub;
    }

    protected function createFeatureTestSubDirectoryFor($modelName)
    {
        if (!is_dir(base_path('tests/Feature/' . str_plural($modelName)))) {
            mkdir(base_path('tests/Feature/' . str_plural($modelName)));
        }
    }

    protected function createModelFactory($modelName, $attributes)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/factory.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        $formattedAttributes = $attributes->map(function ($attribute) {
            return "'" . $attribute . "'" . ' => $faker->word';
        });

        $stub = str_replace('ATTRIBUTES', $formattedAttributes->implode(',' . PHP_EOL . "\t\t"), $stub);

        file_put_contents(base_path('database/factories/' . $modelName . 'Factory.php'), $stub);
    }

    protected function createMigration($modelName, $attributes)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/migration.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        $formattedAttributes = $attributes->map(function ($attribute) {
            return '$table->string(' . "'" . strtolower($attribute) . "')->nullable();";
        });

        $stub = str_replace('ATTRIBUTES', $formattedAttributes->implode(PHP_EOL . "\t\t\t"), $stub);

        $now = now();

        $fileName = $now->format('Y') . '_' . $now->format('m') . '_' . $now->format('d') . '_' . $now->format('His');

        $fileName = $fileName . '_create_' . str_plural(strtolower($modelName)) . '_table.php';

        file_put_contents(base_path("database/migrations/{$fileName}"), $stub);
    }

    public function createViews($modelName, $attributes)
    {
        $viewPath = base_path('resources/views/' . str_plural(strtolower($modelName)));

        $this->createDirectory($viewPath);

        $this->createDirectory($viewPath . '/partials');

        $this->createCreateView($modelName);

        $this->createEditView($modelName);

        $this->createShowView($modelName);

        $this->createIndexView($modelName);

        $this->createFormView($modelName, $attributes);

        $this->createModelView($modelName, $attributes);
    }

    protected function createCreateView($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/create_view.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(base_path('resources/views/' . str_plural(strtolower($modelName)) . '/create.blade.php'), $stub);
    }

    protected function createEditView($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/edit_view.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(base_path('resources/views/' . str_plural(strtolower($modelName)) . '/edit.blade.php'), $stub);
    }

    protected function createShowView($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/show_view.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(base_path('resources/views/' . str_plural(strtolower($modelName)) . '/show.blade.php'), $stub);
    }

    protected function createIndexView($modelName)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/index_view.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        file_put_contents(base_path('resources/views/' . str_plural(strtolower($modelName)) . '/index.blade.php'), $stub);
    }

    protected function createFormView($modelName, $attributes)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/form_view.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        $formattedAttributes = $attributes->map(function ($attribute) use ($modelName) {
            return "\t" . '<div>' . PHP_EOL
                . "\t\t" . '<label for="' . $attribute . '">' . ucfirst(str_replace('_', ' ', $attribute)) . '</label>'
                . PHP_EOL
                . "\t\t" . '<input type="text" name="' . $attribute . '" class="form-control" value="{{ $' . strtolower($modelName) . "->{$attribute} ?? '' }}" . '" />'
                . PHP_EOL . "\t" . '</div>'
                . PHP_EOL;
        })->implode(PHP_EOL);

        $stub = str_replace('ATTRIBUTES', $formattedAttributes, $stub);

        file_put_contents(base_path('resources/views/' . str_plural(strtolower($modelName)) . '/partials/form.blade.php'), $stub);
    }

    protected function createModelView($modelName, $attributes)
    {
        $stub = file_get_contents(base_path('vendor/jasrys/laravel-crud/stubs/model_view.stub'));

        $stub = $this->replacePlaceholdersWithModelName($stub, $modelName);

        $formattedAttributes = $attributes->map(function ($attribute) use ($modelName) {
            return "\t" . '<div>'
                . PHP_EOL . "\t\t" . '{{ $' . strtolower($modelName) . "->{$attribute}" . ' }}'
                . PHP_EOL . "\t" . '</div>'
                . PHP_EOL;
        })->implode(PHP_EOL);

        $stub = str_replace('ATTRIBUTES', $formattedAttributes, $stub);

        file_put_contents(base_path('resources/views/' . str_plural(strtolower($modelName)) . '/partials/' . strtolower($modelName) . '.blade.php'), $stub);
    }

    protected function createResourceRoutes($modelName)
    {
        $routesFile = file_get_contents(base_path('routes/web.php'));

        $routesFile .= PHP_EOL
            . "Route::resource('" . str_plural(strtolower($modelName)) . "', '" . $modelName . "Controller')->middleware('auth');"
            . PHP_EOL;

        file_put_contents(base_path('routes/web.php'), $routesFile);
    }

    protected function createDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path);
        }
    }
}
