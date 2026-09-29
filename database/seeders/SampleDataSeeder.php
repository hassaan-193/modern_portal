<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\StafProfile;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\Memo;
use App\Models\MemoAcknowledgment;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // ----------------------------------------------------
        // 1. Roles & Permissions Setup
        // ----------------------------------------------------
        $roles = [
            'Super-User',
            'Staff',
            'Accounts',
            'HR',
            'Foreman',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $superRole = Role::where('name', 'Super-User')->first();
        if ($superRole) {
            $superRole->givePermissionTo(Permission::all());
        }

        $staffRole = Role::where('name', 'Staff')->first();
        if ($staffRole) {
            $staffPerms = Permission::whereIn('name', ['view_memos', 'requestForms', 'users'])->get();
            $staffRole->syncPermissions($staffPerms);
        }

        $accountsRole = Role::where('name', 'Accounts')->first();
        if ($accountsRole) {
            $accountsPerms = Permission::whereIn('name', [
                'view_memos', 'invoices', 'paymentInvoices', 'accounts', 'receipts', 'payments', 'cheques', 'ticket_status'
            ])->get();
            $accountsRole->syncPermissions($accountsPerms);
        }

        $hrRole = Role::where('name', 'HR')->first();
        if ($hrRole) {
            $hrPerms = Permission::whereIn('name', [
                'view_memos', 'create_memos', 'manage_memos', 'employees', 'stafprofile', 'document', 'payroll', 'ticket_creation'
            ])->get();
            $hrRole->syncPermissions($hrPerms);
        }

        $foremanRole = Role::where('name', 'Foreman')->first();
        if ($foremanRole) {
            $foremanPerms = Permission::whereIn('name', [
                'view_memos', 'labor_attendance', 'projects', 'tasks'
            ])->get();
            $foremanRole->syncPermissions($foremanPerms);
        }

        // ----------------------------------------------------
        // 2. Users (Registrations)
        // ----------------------------------------------------
        $userData = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'role' => 'Super-User',
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@user.com',
                'role' => 'Staff',
            ],
            [
                'name' => 'Staff Member',
                'email' => 'staff@example.com',
                'role' => 'Staff',
            ],
            [
                'name' => 'Admin Test',
                'email' => 'admin_test@example.com',
                'role' => 'Super-User',
            ],
            [
                'name' => 'Staff Test',
                'email' => 'staff_test@example.com',
                'role' => 'Staff',
            ],
            [
                'name' => 'Accounts Officer',
                'email' => 'accounts@example.com',
                'role' => 'Accounts',
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@example.com',
                'role' => 'HR',
            ],
            [
                'name' => 'Site Foreman',
                'email' => 'foreman@example.com',
                'role' => 'Foreman',
            ],
        ];

        $createdUsers = [];
        foreach ($userData as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'api_token' => Str::random(60),
                ]
            );

            if (isset($u['role']) && method_exists($user, 'assignRole')) {
                $user->syncRoles([$u['role']]);
            }
            $createdUsers[$u['email']] = $user;
        }

        $admin = $createdUsers['admin@example.com'] ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        // ----------------------------------------------------
        // 3. Companies / Clients
        // ----------------------------------------------------
        $c1 = Company::firstOrCreate(['name' => 'Al Futtaim Engineering'], [
            'email' => 'projects@alfuttaim-sample.ae',
            'contact_person' => 'Ahmed Al-Maktoum',
            'contact_no' => '+971501234567',
            'vat_no' => '100234567800003',
            'location' => 'Dubai Silicon Oasis, Dubai, UAE',
            'billing_address' => 'Floor 12, Tower B, Business Bay, Dubai',
            'billing_contact_person' => 'Ahmed Al-Maktoum',
            'billing_email' => 'accounts@alfuttaim-sample.ae',
        ]);

        $c2 = Company::firstOrCreate(['name' => 'Emaar Properties PJSC'], [
            'email' => 'facilities@emaar-sample.ae',
            'contact_person' => 'Sarah Jenkins',
            'contact_no' => '+971509876543',
            'vat_no' => '100456789000003',
            'location' => 'Downtown Dubai, UAE',
            'billing_address' => 'Emaar Square, Bldg 3, Downtown Dubai',
            'billing_contact_person' => 'Sarah Jenkins',
            'billing_email' => 'billing@emaar-sample.ae',
        ]);

        $c3 = Company::firstOrCreate(['name' => 'Damac Properties'], [
            'email' => 'info@damac-sample.ae',
            'contact_person' => 'Khalid Mansoor',
            'contact_no' => '+971552233445',
            'vat_no' => '100987654300003',
            'location' => 'Damac Hills, Dubai, UAE',
            'billing_address' => 'Damac Executive Heights, Barsha Heights, Dubai',
            'billing_contact_person' => 'Khalid Mansoor',
            'billing_email' => 'finance@damac-sample.ae',
        ]);

        $c4 = Company::firstOrCreate(['name' => 'Nakheel PJSC'], [
            'email' => 'projects@nakheel-sample.ae',
            'contact_person' => 'Omar Al-Hashimi',
            'contact_no' => '+971503344556',
            'vat_no' => '100876543200003',
            'location' => 'Palm Jumeirah, Dubai, UAE',
            'billing_address' => 'Nakheel Mall Office Tower, Dubai',
            'billing_contact_person' => 'Omar Al-Hashimi',
            'billing_email' => 'billing@nakheel-sample.ae',
        ]);

        // ----------------------------------------------------
        // 4. Vendors
        // ----------------------------------------------------
        Vendor::firstOrCreate(['name' => 'Hikvision Middle East'], [
            'email' => 'sales@hikvision-sample.ae',
            'contact_person' => 'Michael Chen',
            'contact_no' => '+97148881234',
            'vat_no' => '100111222300003',
            'location' => 'JAFZA South, Dubai',
            'payment_preference' => 'Transfer',
            'vendor_specialization' => 'CCTV and Surveillance',
        ]);

        Vendor::firstOrCreate(['name' => 'Ducab Cable Solutions'], [
            'email' => 'orders@ducab-sample.ae',
            'contact_person' => 'Rami Haddad',
            'contact_no' => '+97148885678',
            'vat_no' => '100222333400003',
            'location' => 'Industrial Area 1, Jebel Ali, Dubai',
            'payment_preference' => 'Cheque',
            'vendor_specialization' => 'Cables & Networking',
        ]);

        // ----------------------------------------------------
        // 5. Products
        // ----------------------------------------------------
        $productDefs = [
            ['name' => 'Hikvision 4MP IP Dome Camera (DS-2CD2143G2-I)', 'price' => 380.00],
            ['name' => 'Hikvision 16-Ch 4K NVR with PoE (DS-7616NI-I2/16P)', 'price' => 1450.00],
            ['name' => 'Schneider 42U Server Rack Cabinet 800x1000', 'price' => 2800.00],
            ['name' => 'Ducab Cat6 UTP Cable Drum 305m (LSZH)', 'price' => 420.00],
            ['name' => 'ZKTeco ProCapture-T Biometric Access Controller', 'price' => 890.00],
            ['name' => 'Cisco Catalyst 24-Port Gigabit PoE+ Switch', 'price' => 3200.00],
        ];

        $productModels = [];
        foreach ($productDefs as $p) {
            $productModels[] = Product::firstOrCreate(['name' => $p['name']], ['price' => $p['price']]);
        }

        // ----------------------------------------------------
        // 6. Quotations & Quotation Products
        // ----------------------------------------------------
        $q1 = Quotation::firstOrCreate(
            ['ref_no' => 'QUO-2026-001'],
            [
                'name' => '(Al Futtaim Engineering) (Burj View CCTV)',
                'company_id' => $c1->id,
                'amount' => 45000.00,
                'vat' => 2250.00,
                'total_amount' => 47250.00,
                'date' => Carbon::now()->subDays(7)->toDateString(),
                'subject' => 'IP CCTV Surveillance & Access Control System Supply & Installation',
                'location' => 'Dubai Silicon Oasis HQ',
                'payment' => "<ul>\n<li>100% Advance payment (Non-Refundable) - TRN: 100317831400003</li>\n<li>Payment/Cheque within 7 working days of invoice.</li>\n<li>Validity: 14 days</li>\n</ul>",
                'exclusion' => "<ul>\n<li>Civil works, power points, core cutting & painting works.</li>\n<li>Any third-party regulatory charges.</li>\n</ul>",
                'status' => 1, // Approved
                'category' => 'CCTV & Security',
                'number_of_visits' => 4,
            ]
        );

        if ($q1->wasRecentlyCreated && count($productModels) >= 4) {
            QuotationProduct::create([
                'quotation_id' => $q1->id,
                'product_id' => $productModels[0]->id,
                'quantity' => 12,
                'unit_price' => 380.00,
                'total_price' => 4560.00,
            ]);
            QuotationProduct::create([
                'quotation_id' => $q1->id,
                'product_id' => $productModels[1]->id,
                'quantity' => 2,
                'unit_price' => 1450.00,
                'total_price' => 2900.00,
            ]);
            QuotationProduct::create([
                'quotation_id' => $q1->id,
                'product_id' => $productModels[2]->id,
                'quantity' => 1,
                'unit_price' => 2800.00,
                'total_price' => 2800.00,
            ]);
            QuotationProduct::create([
                'quotation_id' => $q1->id,
                'product_id' => $productModels[3]->id,
                'quantity' => 8,
                'unit_price' => 420.00,
                'total_price' => 3360.00,
            ]);
        }

        $q2 = Quotation::firstOrCreate(
            ['ref_no' => 'QUO-2026-002'],
            [
                'name' => '(Emaar Properties PJSC) (Downtown BMS AMC)',
                'company_id' => $c2->id,
                'amount' => 115000.00,
                'vat' => 5750.00,
                'total_amount' => 120750.00,
                'date' => Carbon::now()->subDays(3)->toDateString(),
                'subject' => 'Annual Maintenance Contract for BMS & Fire Alarm Systems',
                'location' => 'Downtown Dubai Boulevard',
                'payment' => "<ul>\n<li>Quarterly in advance against invoice.</li>\n<li>Validity: 30 days</li>\n</ul>",
                'exclusion' => "<ul>\n<li>Replacement of hardware damaged by water seepage or surges.</li>\n</ul>",
                'status' => 0, // Pending
                'category' => 'AMC',
                'number_of_visits' => 12,
            ]
        );

        if ($q2->wasRecentlyCreated && count($productModels) >= 6) {
            QuotationProduct::create([
                'quotation_id' => $q2->id,
                'product_id' => $productModels[4]->id,
                'quantity' => 8,
                'unit_price' => 890.00,
                'total_price' => 7120.00,
            ]);
            QuotationProduct::create([
                'quotation_id' => $q2->id,
                'product_id' => $productModels[5]->id,
                'quantity' => 4,
                'unit_price' => 3200.00,
                'total_price' => 12800.00,
            ]);
        }

        $q3 = Quotation::firstOrCreate(
            ['ref_no' => 'QUO-2026-003'],
            [
                'name' => '(Damac Properties) (Damac Hills Gate Barrier)',
                'company_id' => $c3->id,
                'amount' => 38000.00,
                'vat' => 1900.00,
                'total_amount' => 39900.00,
                'date' => Carbon::now()->subDays(1)->toDateString(),
                'subject' => 'RFID Gate Barrier & Automated Boom Arms Installation',
                'location' => 'Damac Hills Main Gate',
                'payment' => "<ul>\n<li>50% Advance with Purchase Order.</li>\n<li>50% on Handover & Testing.</li>\n</ul>",
                'exclusion' => "<ul>\n<li>Road cutting, trenching & municipality permits.</li>\n</ul>",
                'status' => 0, // Pending
                'category' => 'Access Control',
                'number_of_visits' => 2,
            ]
        );

        // ----------------------------------------------------
        // 7. Projects
        // ----------------------------------------------------
        $projectType = ProjectType::first();
        $projectTypeId = $projectType ? $projectType->id : 1;

        Project::firstOrCreate(['subject' => 'Burj View Tower CCTV Upgrade'], [
            'date' => Carbon::now()->subMonths(1)->toDateString(),
            'project_type_id' => $projectTypeId,
            'payment_terms' => '50% Advance, 40% on Delivery, 10% on Handover',
            'labour_charges' => 8500,
            'material_charges' => 24000,
            'project_source' => 'Direct Client',
            'project_estimation' => '32500',
            'user_id' => $adminId,
            'note' => 'Installation of 32 HD IP Cameras, 2 NVRs, and fiber optic backbone cable.',
            'category' => 'CCTV & Security',
            'visits' => 6,
        ]);

        Project::firstOrCreate(['subject' => 'Downtown Boulevard Access Control AMC'], [
            'date' => Carbon::now()->subDays(15)->toDateString(),
            'project_type_id' => $projectTypeId,
            'payment_terms' => 'Quarterly in advance',
            'labour_charges' => 12000,
            'material_charges' => 18000,
            'project_source' => 'Contract Renewal',
            'project_estimation' => '30000',
            'user_id' => $adminId,
            'note' => 'Comprehensive monthly preventive maintenance across all access control doors.',
            'category' => 'AMC',
            'visits' => 12,
        ]);

        // ----------------------------------------------------
        // 8. Staff Profiles
        // ----------------------------------------------------
        StafProfile::firstOrCreate(['name' => 'Tariq', 'last_name' => 'Mahmood'], [
            'staf_type' => 'Staff',
            'nationality' => 'Pakistani',
            'gender' => 'Male',
            'joining_date' => Carbon::now()->subYears(2)->toDateString(),
            'dob' => '1990-05-15',
            'passport_expiry' => Carbon::now()->addYears(2)->toDateString(),
            'visa_expiry' => Carbon::now()->addMonths(8)->toDateString(),
            'emirates_id_expiry' => Carbon::now()->addMonths(10)->toDateString(),
            'basic_salary' => 6000,
            'total_salary' => 8500,
            'overtime_rate' => 35.0,
            'mobile_no' => '+971501122334',
        ]);

        StafProfile::firstOrCreate(['name' => 'Rashid', 'last_name' => 'Khan'], [
            'staf_type' => 'Labor',
            'nationality' => 'Indian',
            'gender' => 'Male',
            'joining_date' => Carbon::now()->subYear()->toDateString(),
            'dob' => '1995-08-20',
            'passport_expiry' => Carbon::now()->addYear()->toDateString(),
            'visa_expiry' => Carbon::now()->addMonths(6)->toDateString(),
            'emirates_id_expiry' => Carbon::now()->addMonths(6)->toDateString(),
            'basic_salary' => 3000,
            'total_salary' => 4200,
            'overtime_rate' => 20.0,
            'mobile_no' => '+971559988776',
        ]);

        // ----------------------------------------------------
        // 9. Inquiries
        // ----------------------------------------------------
        Inquiry::firstOrCreate(['inquiry_no' => 'INQ-2026-001'], [
            'created_by' => $adminId,
            'client_name' => 'Al Futtaim Engineering',
            'phone' => '+971501234567',
            'email' => 'projects@alfuttaim-sample.ae',
            'location' => 'Dubai Silicon Oasis HQ',
            'inquiry_type' => 'Project',
            'source' => 'Email',
            'expected_price' => 45000.00,
            'status' => 'New',
            'priority' => 'High',
            'follow_up_date' => Carbon::now()->addDays(3)->toDateString(),
            'expected_closing_date' => Carbon::now()->addMonth()->toDateString(),
            'notes' => 'Complete IP CCTV surveillance and access control setup for new facility wing.',
        ]);

        Inquiry::firstOrCreate(['inquiry_no' => 'INQ-2026-002'], [
            'created_by' => $adminId,
            'client_name' => 'Emaar Properties PJSC',
            'phone' => '+971509876543',
            'email' => 'facilities@emaar-sample.ae',
            'location' => 'Downtown Boulevard',
            'inquiry_type' => 'AMC',
            'source' => 'Referral',
            'expected_price' => 120000.00,
            'status' => 'Assigned',
            'priority' => 'Medium',
            'follow_up_date' => Carbon::now()->addDays(5)->toDateString(),
            'expected_closing_date' => Carbon::now()->addMonths(2)->toDateString(),
            'notes' => 'Annual Maintenance Contract for BMS and fire alarms across 3 residential towers.',
        ]);

        // ----------------------------------------------------
        // 10. Memos & Acknowledgment System
        // ----------------------------------------------------
        $m1 = Memo::firstOrCreate(
            ['reference_number' => 'FTS-MEMO-2026-001'],
            [
                'title' => 'Annual Health & Safety Workplace Policy Update 2026',
                'description' => 'Mandatory review of revised fire safety guidelines, site PPE requirements, and incident reporting protocols for all technical and on-site personnel.',
                'category' => 'policy',
                'file_path' => 'memos/sample_company_safety_memo_2026.pdf',
                'file_name' => 'sample_company_safety_memo_2026.pdf',
                'file_type' => 'pdf',
                'file_size' => 4808,
                'uploaded_by' => $adminId,
                'recipient_type' => 'all',
                'memo_date' => Carbon::now()->subDays(14)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(14)->toDateTimeString(),
            ]
        );

        $m2 = Memo::firstOrCreate(
            ['reference_number' => 'FTS-MEMO-2026-002'],
            [
                'title' => 'Site Security and Digital Access Card Protocols',
                'description' => 'New access card protocols for all server rooms, customer project sites, and technical labs. Lost cards must be reported within 2 hours.',
                'category' => 'security',
                'file_path' => 'memos/fts_memo_2026_002_it_security.pdf',
                'file_name' => 'fts_memo_2026_002_it_security.pdf',
                'file_type' => 'pdf',
                'file_size' => 4731,
                'uploaded_by' => $adminId,
                'recipient_type' => 'all',
                'memo_date' => Carbon::now()->subDays(9)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(9)->toDateTimeString(),
            ]
        );

        $m3 = Memo::firstOrCreate(
            ['reference_number' => 'FTS-MEMO-2026-003'],
            [
                'title' => 'Holiday Operations & On-Call Engineering Schedule',
                'description' => 'Holiday schedule and emergency technical escalation contacts for active site maintenance contracts across Dubai and Northern Emirates.',
                'category' => 'announcement',
                'file_path' => 'memos/fts_memo_2026_003_hr_scheduling.pdf',
                'file_name' => 'fts_memo_2026_003_hr_scheduling.pdf',
                'file_type' => 'pdf',
                'file_size' => 4746,
                'uploaded_by' => $adminId,
                'recipient_type' => 'all',
                'memo_date' => Carbon::now()->subDays(2)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(2)->toDateTimeString(),
            ]
        );

        // Seed Acknowledgments for Memo 1 and Memo 2
        $ackUsers = [
            $createdUsers['admin@example.com'] ?? null,
            $createdUsers['user@user.com'] ?? null,
            $createdUsers['staff@example.com'] ?? null,
        ];

        foreach ($ackUsers as $u) {
            if ($u && $m1) {
                MemoAcknowledgment::firstOrCreate(
                    [
                        'memo_id' => $m1->id,
                        'user_id' => $u->id,
                    ],
                    [
                        'acknowledged_at' => Carbon::now()->subDays(10),
                        'ip_address' => '192.168.1.10' . $u->id,
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'confirmation_statement' => 'I confirm that I have read and carefully understood this memo.',
                    ]
                );
            }
        }

        if (isset($createdUsers['admin@example.com']) && $m2) {
            MemoAcknowledgment::firstOrCreate(
                [
                    'memo_id' => $m2->id,
                    'user_id' => $createdUsers['admin@example.com']->id,
                ],
                [
                    'acknowledged_at' => Carbon::now()->subDays(5),
                    'ip_address' => '192.168.1.101',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0',
                    'confirmation_statement' => 'I confirm that I have read and carefully understood this memo.',
                ]
            );
        }
    }
}
