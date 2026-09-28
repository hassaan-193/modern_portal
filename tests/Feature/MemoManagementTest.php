<?php

namespace Tests\Feature;

use App\Models\Memo;
use App\Models\MemoAcknowledgment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemoManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'fts_portal',
        ]);
        Storage::fake('public');

        // Create or fetch admin user
        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@example.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password')]
        );

        if (class_exists(Role::class)) {
            $superRole = Role::firstOrCreate(['name' => 'Super-User', 'guard_name' => 'web']);
            if (method_exists($this->admin, 'assignRole')) {
                $this->admin->assignRole($superRole);
            }
        }

        // Create or fetch regular staff user
        $this->regularUser = User::firstOrCreate(
            ['email' => 'staff_test@example.com'],
            ['name' => 'Staff Test', 'password' => bcrypt('password')]
        );
    }

    protected function tearDown(): void
    {
        // Clean up test memos created during test runs
        $testMemos = Memo::withTrashed()
            ->where(function ($q) {
                $q->where('reference_number', 'like', 'FTS-MEMO-TEST%')
                  ->orWhereNull('reference_number')
                  ->orWhere('title', 'like', '%Test%');
            })
            ->pluck('id');

        if ($testMemos->isNotEmpty()) {
            MemoAcknowledgment::whereIn('memo_id', $testMemos)->delete();
            Memo::withTrashed()->whereIn('id', $testMemos)->forceDelete();
        }

        parent::tearDown();
    }

    /** @test */
    public function unauthenticated_users_are_redirected_to_login_preserving_intended_url()
    {
        $memo = Memo::first();
        if (!$memo) {
            $this->markTestSkipped('No memos available to test unauthenticated redirect.');
        }

        $response = $this->get(route('memos.show', $memo->id));
        $response->assertRedirect(route('login'));
        $this->assertEquals(url(route('memos.show', $memo->id)), session('url.intended'));
    }

    /** @test */
    public function authenticated_user_can_view_memos_index()
    {
        $response = $this->actingAs($this->regularUser)->get(route('memos.index'));
        $response->assertStatus(200);
        $response->assertSee('Memos & Letters', false);
    }

    /** @test */
    public function authorized_user_can_upload_a_valid_memo()
    {
        // Grant permission explicitly to avoid role caching discrepancies
        if (method_exists($this->admin, 'givePermissionTo')) {
            $this->admin->givePermissionTo('create_memos');
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $file = UploadedFile::fake()->create('Company_Safety_Notice.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)->post(route('memos.store'), [
            'title'            => 'Quarterly Fire Safety Review',
            'reference_number' => 'FTS-MEMO-TEST-' . time(),
            'category'         => 'safety',
            'memo_date'        => now()->toDateString(),
            'recipient_type'   => 'all',
            'description'      => 'Detailed instructions for field crews.',
            'file'             => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('memos', [
            'title'    => 'Quarterly Fire Safety Review',
            'category' => 'safety',
        ]);
    }

    /** @test */
    public function invalid_or_executable_file_types_are_rejected()
    {
        if (method_exists($this->admin, 'givePermissionTo')) {
            $this->admin->givePermissionTo('create_memos');
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $dangerousFile = UploadedFile::fake()->create('malicious_script.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($this->admin)->post(route('memos.store'), [
            'title'            => 'Harmful Memo',
            'category'         => 'general',
            'memo_date'        => now()->toDateString(),
            'recipient_type'   => 'all',
            'file'             => $dangerousFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function acknowledgment_requires_mandatory_confirmation_checkbox()
    {
        $memo = Memo::create([
            'title'          => 'Test Policy Document',
            'category'       => 'policy',
            'file_path'      => 'memos/test.pdf',
            'file_name'      => 'test.pdf',
            'file_type'      => 'pdf',
            'uploaded_by'    => $this->admin->id,
            'recipient_type' => 'all',
            'memo_date'      => now()->toDateString(),
            'status'         => 'published',
        ]);

        // Attempt acknowledgment without checking the mandatory box
        $response = $this->actingAs($this->regularUser)
            ->post(route('memos.acknowledge', $memo->id), [
                'confirm_understood' => '0',
            ]);

        $response->assertSessionHasErrors(['confirm_understood']);
        $this->assertFalse($memo->fresh()->isAcknowledgedBy($this->regularUser));
    }

    /** @test */
    public function user_can_successfully_acknowledge_a_memo()
    {
        $memo = Memo::create([
            'title'          => 'Acknowledged Test Policy',
            'category'       => 'policy',
            'file_path'      => 'memos/test.pdf',
            'file_name'      => 'test.pdf',
            'file_type'      => 'pdf',
            'uploaded_by'    => $this->admin->id,
            'recipient_type' => 'all',
            'memo_date'      => now()->toDateString(),
            'status'         => 'published',
        ]);

        // Submit acknowledgment with confirmation checkbox checked
        $response = $this->actingAs($this->regularUser)
            ->post(route('memos.acknowledge', $memo->id), [
                'confirm_understood' => '1',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('memos.show', $memo->id));

        $this->assertTrue($memo->fresh()->isAcknowledgedBy($this->regularUser));
        $this->assertDatabaseHas('memo_acknowledgments', [
            'memo_id' => $memo->id,
            'user_id' => $this->regularUser->id,
        ]);
    }

    /** @test */
    public function duplicate_acknowledgments_are_prevented()
    {
        $memo = Memo::create([
            'title'          => 'Duplicate Prevention Memo',
            'category'       => 'policy',
            'file_path'      => 'memos/test.pdf',
            'file_name'      => 'test.pdf',
            'file_type'      => 'pdf',
            'uploaded_by'    => $this->admin->id,
            'recipient_type' => 'all',
            'memo_date'      => now()->toDateString(),
            'status'         => 'published',
        ]);

        // First acknowledgment
        MemoAcknowledgment::create([
            'memo_id'                => $memo->id,
            'user_id'                => $this->regularUser->id,
            'acknowledged_at'        => now(),
            'ip_address'             => '127.0.0.1',
            'confirmation_statement' => 'I confirm that I have read and carefully understood this memo.',
        ]);

        // Attempt second acknowledgment
        $response = $this->actingAs($this->regularUser)
            ->post(route('memos.acknowledge', $memo->id), [
                'confirm_understood' => '1',
            ]);

        $response->assertSessionHas('info');
        // Count remains 1
        $this->assertEquals(1, $memo->acknowledgments()->where('user_id', $this->regularUser->id)->count());
    }

    /** @test */
    public function unauthorized_user_cannot_access_a_targeted_memo()
    {
        // Memo strictly targeted to specific user ID that is NOT regularUser
        $memo = Memo::create([
            'title'          => 'Confidential Executive Memo',
            'category'       => 'hr',
            'file_path'      => 'memos/confidential.pdf',
            'file_name'      => 'confidential.pdf',
            'file_type'      => 'pdf',
            'uploaded_by'    => $this->admin->id,
            'recipient_type' => 'users',
            'recipient_ids'  => [99999], // restricted to non-existent user
            'memo_date'      => now()->toDateString(),
            'status'         => 'published',
        ]);

        // Regular user should get 403 Forbidden
        $response = $this->actingAs($this->regularUser)->get(route('memos.show', $memo->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function whatsapp_share_url_is_properly_formatted()
    {
        $memo = Memo::first();
        if ($memo) {
            $url = $memo->whatsAppShareUrl();
            $this->assertStringContainsString('https://api.whatsapp.com/send?text=', $url);
            $this->assertStringContainsString(urlencode(route('memos.show', $memo->id)), $url);
        }
    }
}
