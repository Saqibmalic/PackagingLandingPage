<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per enquiry, written in two passes.
     *
     * Stage 1 (contact details) creates the row — that is the lead, and it
     * exists whether or not the buyer ever fills in the spec form. Stage 2
     * updates the same row with box specs and artwork, so a single enquiry
     * never turns into two records in the dashboard.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();

            // Stage 1 — the contact.
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('quantity');

            // Stage 2 — the box.
            $table->timestamp('specs_added_at')->nullable();
            $table->string('length')->nullable();
            $table->string('width')->nullable();
            $table->string('depth')->nullable();
            $table->string('units')->nullable();
            $table->string('style')->nullable();
            $table->string('board')->nullable();
            $table->string('wrap')->nullable();
            $table->string('insert')->nullable();
            $table->json('finish')->nullable();
            $table->string('second_quantity')->nullable();
            $table->date('need_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('files')->nullable();

            // Ad click context. gclid is what powers offline conversion
            // imports back into Google Ads, so it is indexed.
            $table->string('gclid')->nullable()->index();
            $table->string('gbraid')->nullable();
            $table->string('wbraid')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('page_url', 512)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            // Pipeline.
            $table->string('status')->default('new')->index();
            $table->decimal('value', 10, 2)->default(0);
            $table->text('admin_notes')->nullable();
            $table->timestamp('exported_at')->nullable();

            $table->timestamps();

            // The dashboard's default view is "newest first", and every filter
            // narrows that same ordering.
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
