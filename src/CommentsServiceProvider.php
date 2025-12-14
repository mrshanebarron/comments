<?php

namespace MrShaneBarron\Comments;

use Illuminate\Support\ServiceProvider;
use MrShaneBarron\Comments\View\Components\Comments;
use MrShaneBarron\Comments\View\Components\Comment;
use MrShaneBarron\Comments\View\Components\CommentForm;
use Livewire\Livewire;

class CommentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sb-comments.php', 'sb-comments');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sb-comments');

        $this->publishes([
            __DIR__.'/../config/sb-comments.php' => config_path('sb-comments.php'),
        ], 'sb-comments-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/sb-comments'),
        ], 'sb-comments-views');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'sb-comments-migrations');

        // Register Blade components
        $this->loadViewComponentsAs('ld', [
            Comments::class,
            Comment::class,
            CommentForm::class,
        ]);

        // Register Livewire component if Livewire is installed
        if (class_exists(Livewire::class)) {
            Livewire::component('sb-comments', \MrShaneBarron\Comments\Livewire\Comments::class);
        }
    }
}
