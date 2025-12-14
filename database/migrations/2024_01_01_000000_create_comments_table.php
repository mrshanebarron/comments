<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('ld-comments.table', 'comments'), function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained(config('ld-comments.table', 'comments'))->cascadeOnDelete();
            $table->text('body');
            $table->string('guest_name')->nullable();
            $table->boolean('approved')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id']);
            $table->index('parent_id');
            $table->index('approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('ld-comments.table', 'comments'));
    }
};
