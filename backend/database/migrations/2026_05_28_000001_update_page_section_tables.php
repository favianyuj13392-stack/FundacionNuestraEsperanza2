<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('about_us_sections', function (Blueprint $table) {
            $table->integer('order')->default(1)->after('is_active');
            $table->string('title')->nullable()->after('name');
            $table->string('subtitle')->nullable()->after('title');
            $table->text('content')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('content');
            $table->string('meta_title')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });

        Schema::table('programs_sections', function (Blueprint $table) {
            $table->string('name')->default('Programs Section')->after('id');
            $table->string('identifier')->default('programs')->after('name');
            $table->boolean('is_active')->default(true)->after('identifier');
            $table->integer('order')->default(1)->after('is_active');
            $table->string('title')->nullable()->after('order');
            $table->string('subtitle')->nullable()->after('title');
            $table->text('content')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('content');
            $table->string('meta_title')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });

        Schema::table('testimonials_sections', function (Blueprint $table) {
            $table->string('name')->default('Testimonials Section')->after('id');
            $table->string('identifier')->default('testimonials')->after('name');
            $table->boolean('is_active')->default(true)->after('identifier');
            $table->integer('order')->default(1)->after('is_active');
            $table->string('title')->nullable()->after('order');
            $table->string('subtitle')->nullable()->after('title');
            $table->text('content')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('content');
            $table->string('meta_title')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });

        Schema::table('news_sections', function (Blueprint $table) {
            $table->string('name')->default('News Section')->after('id');
            $table->string('identifier')->default('news')->after('name');
            $table->boolean('is_active')->default(true)->after('identifier');
            $table->integer('order')->default(1)->after('is_active');
            $table->string('title')->nullable()->after('order');
            $table->string('subtitle')->nullable()->after('title');
            $table->text('content')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('content');
            $table->string('meta_title')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });

        Schema::table('how_to_help_sections', function (Blueprint $table) {
            $table->string('name')->default('How To Help Section')->after('id');
            $table->string('identifier')->default('how_to_help')->after('name');
            $table->boolean('is_active')->default(true)->after('identifier');
            $table->integer('order')->default(1)->after('is_active');
            $table->string('title')->nullable()->after('order');
            $table->string('subtitle')->nullable()->after('title');
            $table->text('content')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('content');
            $table->string('meta_title')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });

        DB::table('programs_sections')->update(['name' => 'Programs Section', 'identifier' => 'programs', 'is_active' => true, 'order' => 1]);
        DB::table('testimonials_sections')->update(['name' => 'Testimonials Section', 'identifier' => 'testimonials', 'is_active' => true, 'order' => 1]);
        DB::table('news_sections')->update(['name' => 'News Section', 'identifier' => 'news', 'is_active' => true, 'order' => 1]);
        DB::table('how_to_help_sections')->update(['name' => 'How To Help Section', 'identifier' => 'how_to_help', 'is_active' => true, 'order' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us_sections', function (Blueprint $table) {
            $table->dropColumn(['order', 'title', 'subtitle', 'content', 'image', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('programs_sections', function (Blueprint $table) {
            $table->dropColumn(['name', 'identifier', 'is_active', 'order', 'title', 'subtitle', 'content', 'image', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('testimonials_sections', function (Blueprint $table) {
            $table->dropColumn(['name', 'identifier', 'is_active', 'order', 'title', 'subtitle', 'content', 'image', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('news_sections', function (Blueprint $table) {
            $table->dropColumn(['name', 'identifier', 'is_active', 'order', 'title', 'subtitle', 'content', 'image', 'meta_title', 'meta_description', 'meta_keywords']);
        });

        Schema::table('how_to_help_sections', function (Blueprint $table) {
            $table->dropColumn(['name', 'identifier', 'is_active', 'order', 'title', 'subtitle', 'content', 'image', 'meta_title', 'meta_description', 'meta_keywords']);
        });
    }
};
