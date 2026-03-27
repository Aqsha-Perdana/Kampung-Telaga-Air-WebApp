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
        if (!Schema::hasTable('users')) {
            return;
        }

        $this->addColumnIfMissing('user_id', function (Blueprint $table) {
            $table->string('user_id')->nullable();
        });

        $this->addColumnIfMissing('google_id', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique();
        });

        $this->addColumnIfMissing('provider', function (Blueprint $table) {
            $table->string('provider')->nullable();
        });

        $this->addColumnIfMissing('join_date', function (Blueprint $table) {
            $table->string('join_date')->nullable();
        });

        $this->addColumnIfMissing('last_login', function (Blueprint $table) {
            $table->string('last_login')->nullable();
        });

        $this->addColumnIfMissing('status', function (Blueprint $table) {
            $table->string('status')->nullable();
        });

        $this->addColumnIfMissing('role_name', function (Blueprint $table) {
            $table->string('role_name')->nullable();
        });

        $this->addColumnIfMissing('avatar', function (Blueprint $table) {
            $table->string('avatar')->nullable();
        });

        $this->addColumnIfMissing('position', function (Blueprint $table) {
            $table->string('position')->nullable();
        });

        $this->addColumnIfMissing('department', function (Blueprint $table) {
            $table->string('department')->nullable();
        });

        $this->addColumnIfMissing('line_manager', function (Blueprint $table) {
            $table->string('line_manager')->nullable();
        });

        $this->addColumnIfMissing('seconde_line_manager', function (Blueprint $table) {
            $table->string('seconde_line_manager')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $columns = [
            'user_id',
            'google_id',
            'provider',
            'join_date',
            'last_login',
            'status',
            'role_name',
            'avatar',
            'position',
            'department',
            'line_manager',
            'seconde_line_manager',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function addColumnIfMissing(string $column, \Closure $callback): void
    {
        if (Schema::hasColumn('users', $column)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($callback) {
            $callback($table);
        });
    }
};
