<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_id')->default(1);
            $table->string('name');
            $table->string('brand_logo')->nullable();
            $table->string('status')->default('active');
            $table->integer('data_retention_months')->default(12);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_id')->default(1);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('Team Executive'); // Agency Admin, Client Manager, Team Executive
            $table->json('assigned_clients')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('platform_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('platform');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_successful_call_at')->nullable();
            $table->string('health_status')->default('healthy');
            $table->string('platform_account_id')->nullable();
            $table->unsignedBigInteger('connected_by')->nullable();
            $table->timestamps();
        });

        Schema::create('raw_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('platform');
            $table->string('event_type');
            $table->string('event_hash')->unique();
            $table->json('payload_json');
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('platform');
            $table->string('source_comment_id')->nullable();
            $table->string('source_dm_id')->nullable();
            $table->text('contact_phone')->nullable(); // Encrypted at field level
            $table->string('contact_name')->nullable();
            $table->string('contact_handle')->nullable();
            $table->string('status')->default('new'); // new, contacted, qualified, converted, lost
            $table->string('score')->default('warm'); // hot, warm, cold
            $table->string('source_post_id')->nullable();
            $table->unsignedBigInteger('duplicate_of_lead_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('platform');
            $table->string('trigger_type');
            $table->string('trigger_value')->nullable();
            $table->string('action_type');
            $table->json('reply_template_variants')->nullable();
            $table->json('business_hours_variant')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('failed_actions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->string('connection')->nullable();
            $table->string('queue')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('exception')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('action_type')->nullable();
            $table->integer('attempt_count')->default(0);
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('action_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('platform');
            $table->string('action_type');
            $table->string('target_id')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->integer('attempt_count')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('rate_limit_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('platform');
            $table->timestamp('window_start');
            $table->integer('call_count')->default(0);
            $table->timestamps();
        });

        Schema::create('platform_health', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique();
            $table->integer('consecutive_failures')->default(0);
            $table->string('status')->default('healthy'); // healthy, held
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pii_access_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lead_id');
            $table->string('action'); // view, export
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sla_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->timestamp('sla_deadline');
            $table->unsignedBigInteger('escalated_to')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('linkedin_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('alert_type');
            $table->text('source_url')->nullable();
            $table->string('status')->default('unread');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linkedin_alerts');
        Schema::dropIfExists('sla_escalations');
        Schema::dropIfExists('pii_access_log');
        Schema::dropIfExists('platform_health');
        Schema::dropIfExists('rate_limit_counters');
        Schema::dropIfExists('action_log');
        Schema::dropIfExists('failed_actions');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('raw_events');
        Schema::dropIfExists('platform_connections');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('clients');
    }
};
