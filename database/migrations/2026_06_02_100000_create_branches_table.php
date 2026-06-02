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
        // 1. Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Insert initial branches
        $defaultBranches = [
            'Central - Ciudad de Guatemala',
            'Sucursal Villa Nueva',
            'Sucursal Quetzaltenango',
            'Sucursal Chiquimula',
            'Sucursal Escuintla',
            'Sucursal Cobán',
            'Sucursal Petén',
            'Sucursal Zacapa',
            'Sucursal Mazatenango',
            'Distribución / Bodega Central'
        ];

        foreach ($defaultBranches as $name) {
            DB::table('branches')->insert([
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Add branch_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('department');
        });

        // 4. Migrate existing string data to branch_id relationship
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if (!empty($user->branch)) {
                // Try to find matching branch by exact name
                $branchId = DB::table('branches')->where('name', $user->branch)->value('id');

                // If not found, check case-insensitive or partial match
                if (!$branchId) {
                    $branchId = DB::table('branches')->whereRaw('LOWER(name) = ?', [strtolower($user->branch)])->value('id');
                }

                // If still not found, create a new branch entry to preserve the user's branch information
                if (!$branchId) {
                    $branchId = DB::table('branches')->insertGetId([
                        'name' => $user->branch,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('users')->where('id', $user->id)->update([
                    'branch_id' => $branchId
                ]);
            }
        }

        // 5. Add foreign key constraint and drop original branch string column
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->dropColumn('branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add branch string column to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('branch_id');
        });

        // 2. Restore string values from relationship
        $users = DB::table('users')
            ->join('branches', 'users.branch_id', '=', 'branches.id')
            ->select('users.id', 'branches.name')
            ->get();

        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'branch' => $user->name
            ]);
        }

        // 3. Drop foreign key and relation column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // 4. Drop branches table
        Schema::dropIfExists('branches');
    }
};
