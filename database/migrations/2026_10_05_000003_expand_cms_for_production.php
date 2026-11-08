<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['news', 'pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->string('seo_image')->nullable();
                $table->timestamp('scheduled_at')->nullable()->index();
            });
        }

        foreach (['services', 'projects', 'documents', 'agendas'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('publish_status')->default('DRAFT')->index();
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->string('seo_image')->nullable();
            });
        }

        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('credit')->nullable();
            $table->string('category')->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->text('public_response')->nullable();
            $table->timestamp('notified_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('module')->index();
            $table->string('record_type')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('description');
            $table->json('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
