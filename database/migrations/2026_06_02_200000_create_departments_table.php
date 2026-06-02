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
        // 1. Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Insert initial departments
        $defaultDepartments = [
            'Ventas / Ventas Rutas',
            'Administración',
            'Logística / Despacho / Bodega',
            'Contabilidad y Finanzas',
            'Recursos Humanos',
            'Sistemas / IT',
            'Créditos y Cobros',
            'Operaciones',
            'Servicio al Cliente',
            'Mercadeo',
            'Auditoría Interna'
        ];

        foreach ($defaultDepartments as $name) {
            DB::table('departments')->insert([
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Add department_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('employee_code');
        });

        // 4. Migrate existing string data to department_id relationship
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if (!empty($user->department)) {
                // Try to find matching department by exact name
                $departmentId = DB::table('departments')->where('name', $user->department)->value('id');

                // If not found, check case-insensitive or partial match
                if (!$departmentId) {
                    $departmentId = DB::table('departments')->whereRaw('LOWER(name) = ?', [strtolower($user->department)])->value('id');
                }

                // If still not found, create a new department entry to preserve the user's information
                if (!$departmentId) {
                    $departmentId = DB::table('departments')->insertGetId([
                        'name' => $user->department,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('users')->where('id', $user->id)->update([
                    'department_id' => $departmentId
                ]);
            }
        }

        // 5. Add foreign key constraint and drop original department string column
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->dropColumn('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add department string column to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('department_id');
        });

        // 2. Restore string values from relationship
        $users = DB::table('users')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('users.id', 'departments.name')
            ->get();

        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'department' => $user->name
            ]);
        }

        // 3. Drop foreign key and relation column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        // 4. Drop departments table
        Schema::dropIfExists('departments');
    }
};
