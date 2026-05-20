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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('participante')->after('password');
            $table->string('status')->default('active')->after('role'); // active, pending, blocked
            $table->string('phone')->nullable()->after('status');
            $table->string('employee_code')->nullable()->after('phone');
            $table->string('department')->nullable()->after('employee_code');
            $table->string('branch')->nullable()->after('department');
            $table->string('company')->default('Distribuidora Mariscal')->after('branch');
            $table->boolean('accepted_terms')->default(false)->after('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'phone',
                'employee_code',
                'department',
                'branch',
                'company',
                'accepted_terms',
            ]);
        });
    }
};
