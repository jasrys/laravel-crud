# laravel-crud
CRUD generator package for Laravel

## Why is this better than all the other Laravel CRUD generators?

I needed a package to quickly whip up simple models, resource controllers, migrations, feature tests, factories, routes, and views quickly, and none of the other packages I found fit the way I prefer to structure my projects. 

## Installation

Run `composer require jasrys/laravel-crud`. The `CrudServiceProvider` will be auto-discovered if your version of Laravel supports it. Otherwise, add a reference to `\Jasrys\Crud\CrudServiceProvider::class` in the `providers` array of your `config/app.php` file.

## Creating CRUD Models

This package exposes a single command: `php artisan make:crud`. It accepts a single argument for the name of the model as well as a comma-separated (no space) `--attributes` option for the model's attributes.

For example: `php artisan make:crud Post --attributes=title,body`

## What happens when you run `make:crud` command?

* It creates the model and sets the `fillable` fields with the given attributes
* It creates a model factory for the model with the given attributes. By default, the factory assigns a random `$faker->word` to the attribute.
* It creates a migration for the model. By default, all attributes are assumed to be text fields and you'll need to manually edit the migration for other types. I would accept a PR to allow on-the-fly configuration of the attribute type.
* It appends a resource route for the model to the `routes/web.php` file. By default, it assumes the `auth` middleware will be used.
* It creates a resource controller for the model with sensible default index, create, store, edit, update, and delete methods.
* It creates feature tests for all CRUD actions (viewing index/show/create/edit pages, storing a new model, updating an existing model, and deleting a model). It tests that only logged in users are able to perform these actions.
* It creates views for the create, edit, index, and show pages. The create/edit pages contain labeled inputs for each attributed (again, presumed to be text fields by default). The edit form displays existing attributes.
* It migrates the database
