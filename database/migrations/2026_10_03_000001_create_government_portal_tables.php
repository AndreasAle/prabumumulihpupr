<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('CONTENT_EDITOR')->index();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('Berita');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('thumbnail')->nullable();
            $table->string('author')->default('Humas PUPR');
            $table->string('status')->default('DRAFT')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->text('summary');
            $table->longText('description')->nullable();
            $table->json('requirements')->nullable();
            $table->json('steps')->nullable();
            $table->string('processing_time')->nullable();
            $table->string('cost_information')->nullable();
            $table->string('online_url')->nullable();
            $table->string('officer_contact')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('location');
            $table->string('district')->nullable();
            $table->year('year')->index();
            $table->longText('description');
            $table->decimal('budget', 18, 2)->nullable();
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->string('status')->default('PLANNED')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('contractor')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->year('year')->index();
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->string('file_type')->nullable();
            $table->string('file_size')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime')->index();
            $table->dateTime('end_datetime')->nullable();
            $table->string('location');
            $table->string('status')->default('PUBLISHED');
            $table->timestamps();
        });

        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('value');
            $table->string('suffix')->nullable();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('category')->index();
            $table->string('location');
            $table->longText('description');
            $table->string('attachment')->nullable();
            $table->string('status')->default('NEW')->index();
            $table->text('admin_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('website');
            $table->timestamps();
        });

        Schema::create('ai_knowledge_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('source_type');
            $table->longText('content');
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_key')->unique();
            $table->string('ip_hash')->nullable();
            $table->timestamps();
        });
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ai_chat_sessions')->cascadeOnDelete();
            $table->string('role');
            $table->longText('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('ai_knowledge_sources');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('agendas');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('services');
        Schema::dropIfExists('news');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
