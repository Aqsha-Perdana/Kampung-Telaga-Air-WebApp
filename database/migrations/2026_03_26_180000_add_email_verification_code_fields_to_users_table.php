<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_verification_code')) {
                $table->string('email_verification_code')->nullable()->after('email_verified_at');
            }

            if (!Schema::hasColumn('users', 'email_verification_code_expires_at')) {
                $table->timestamp('email_verification_code_expires_at')->nullable()->after('email_verification_code');
            }

            if (!Schema::hasColumn('users', 'email_verification_sent_at')) {
                $table->timestamp('email_verification_sent_at')->nullable()->after('email_verification_code_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'email_verification_code',
                'email_verification_code_expires_at',
                'email_verification_sent_at',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
