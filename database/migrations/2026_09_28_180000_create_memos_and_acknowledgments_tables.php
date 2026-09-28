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
        if (!Schema::hasTable('memos')) {
            Schema::create('memos', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('reference_number')->nullable()->unique();
                $table->text('description')->nullable();
                $table->string('category', 50)->default('general');
                $table->string('file_path');
                $table->string('file_name');
                $table->string('file_type', 50);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->string('recipient_type', 50)->default('all'); // all, roles, users
                $table->json('recipient_ids')->nullable(); // array of role names or user IDs
                $table->date('memo_date')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->string('status', 50)->default('published'); // draft, published, archived
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['status', 'memo_date']);
            });
        }

        if (!Schema::hasTable('memo_acknowledgments')) {
            Schema::create('memo_acknowledgments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('memo_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('acknowledged_at');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('confirmation_statement')->default('I confirm that I have read and carefully understood this memo.');
                $table->timestamps();

                $table->foreign('memo_id')->references('id')->on('memos')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['memo_id', 'user_id'], 'memo_user_acknowledgment_unique');
            });
        }

        // Seed memo permissions into Spatie permissions table
        $guard = 'web';
        $perms = ['view_memos', 'create_memos', 'manage_memos'];
        foreach ($perms as $permName) {
            $permId = DB::table('permissions')->where('name', $permName)->where('guard_name', $guard)->value('id');
            if (!$permId) {
                $permId = DB::table('permissions')->insertGetId([
                    'name' => $permName,
                    'guard_name' => $guard,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Attach permissions to Super-User if exists
        $adminRoleId = DB::table('roles')->where('name', 'Super-User')->where('guard_name', $guard)->value('id');
        if ($adminRoleId) {
            foreach ($perms as $permName) {
                $permId = DB::table('permissions')->where('name', $permName)->where('guard_name', $guard)->value('id');
                $exists = DB::table('role_has_permissions')->where('permission_id', $permId)->where('role_id', $adminRoleId)->exists();
                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id' => $adminRoleId,
                    ]);
                }
            }
        }

        // Attach view_memos to Staff role if exists
        $staffRoleId = DB::table('roles')->where('name', 'Staff')->where('guard_name', $guard)->value('id');
        if ($staffRoleId) {
            $viewPermId = DB::table('permissions')->where('name', 'view_memos')->where('guard_name', $guard)->value('id');
            $exists = DB::table('role_has_permissions')->where('permission_id', $viewPermId)->where('role_id', $staffRoleId)->exists();
            if (!$exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $viewPermId,
                    'role_id' => $staffRoleId,
                ]);
            }
        }

        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_acknowledgments');
        Schema::dropIfExists('memos');

        DB::table('permissions')->whereIn('name', ['view_memos', 'create_memos', 'manage_memos'])->delete();
        if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
