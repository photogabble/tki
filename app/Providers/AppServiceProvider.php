<?php

namespace Tki\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
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
        JsonResource::withoutWrapping();

        // toSimpleArray macro obtained from article written by Eelco Luurtsema.
        // @see https://medium.com/@eelcoluurtsema/improve-performance-of-laravels-pagination-with-complex-queries-e266a5bb33eb
        QueryBuilder::macro('toSimpleArray', function ($column) {
            $results = $this->onceWithColumns(Arr::wrap($column), function () {
                return $this->processor->processSelect($this, $this->runSelect());
            });

            return array_map(function ($item) use ($column) {
                return $item->{$column};
            }, $results);
        });

        EloquentBuilder::macro('toSimpleArray', function ($column) {
            return $this->query->toSimpleArray($column);
        });
    }
}
