<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * The old password login always set "remember me", so clear the tokens to invalidate those cookies and make sure
     * everyone has to log in through Keycloak.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('remember_token')
            ->update(['remember_token' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * The cleared tokens can't be restored.
     */
    public function down(): void
    {
        //
    }
};
