<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_access_token', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('user_id', 100)->nullable()->default(null);
            $table->string('client_id', 100)->nullable()->default(null);
            $table->json('scopes')->nullable()->default(null);
            $table->tinyInteger('revoked')->default(0);
            $table->dateTime('expires_at')->nullable()->default(null);
            $table->datetimes();
            $table->index('user_id');
            $table->index('client_id');
        });
        Schema::create('oauth_authorization_code', function (Blueprint $table) {
            $table->string('code', 100)->primary();
            $table->string('user_id', 100)->nullable()->default(null);
            $table->string('client_id', 100)->nullable()->default(null);
            $table->json('scopes')->nullable()->default(null);
            $table->dateTime('expires_at')->nullable()->default(null);
            $table->tinyInteger('revoked')->default(0);
            $table->datetimes();
            $table->index('user_id');
            $table->index('client_id');
        });
        Schema::create('oauth_client', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('name', 255)->nullable()->default(null);
            $table->longText('secret')->nullable()->default(null);
            $table->json('scopes')->nullable()->default(null);
            $table->json('redirects')->nullable()->default(null);
            $table->json('grants')->nullable()->default(null);
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('allow_plain_text_pkce')->default(0);
            $table->datetimes();
            $table->index('name');
        });
        Schema::create('oauth_refresh_token', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->tinyInteger('revoked')->default(0);
            $table->datetimes();
            $table->dateTime('expires_at')->nullable()->default(null);
        });
        Schema::create('oauth_device_grant', function (Blueprint $table) {
            $table->string('device_code', 100)->primary();
            $table->string('user_id', 100)->nullable()->default(null);
            $table->string('client_id', 100)->nullable()->default(null);
            $table->json('scopes')->nullable()->default(null);
            $table->string('user_code', 100)->nullable()->default(null);
            $table->string('verification_uri', 255)->nullable()->default(null);
            $table->string('ip_address', 45)->nullable()->default(null);
            $table->string('status', 20)->default('pending');
            $table->dateTime('last_poll_at')->nullable()->default(null);
            $table->dateTime('expires_at')->nullable()->default(null);
            $table->boolean('revoke')->default(false);
            $table->datetimes();
            $table->index('user_id');
            $table->index('client_id');
            $table->index('user_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_refresh_token');
        Schema::dropIfExists('oauth_client');
        Schema::dropIfExists('oauth_authorization_code');
        Schema::dropIfExists('oauth_access_token');
        Schema::dropIfExists('oauth_scope');
        Schema::dropIfExists('oauth_device_grant');
    }
};
