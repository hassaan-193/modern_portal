<?php

namespace Database\Seeders;

use App\Models\Memo;
use App\Models\MemoAcknowledgment;
use App\Notifications\NewMemoPublishedNotification;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SampleMemoSeeder extends Seeder
{
    /**
     * Run the database seeds to generate 3 official, realistic testing memos with PDF documents.
     */
    public function run(): void
    {
        // 1. Clean up old/empty dummy test records
        $validRefs = ['FTS-MEMO-2026-001', 'FTS-MEMO-2026-002', 'FTS-MEMO-2026-003'];
        
        // Remove acknowledgments for memos about to be deleted
        $memosToDelete = Memo::whereNotIn('reference_number', $validRefs)->pluck('id');
        if ($memosToDelete->isNotEmpty()) {
            MemoAcknowledgment::whereIn('memo_id', $memosToDelete)->delete();
            Memo::whereIn('id', $memosToDelete)->forceDelete();
        }

        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('memos');
        $publicDir = public_path('storage/memos');
        if (!File::isDirectory($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }

        $admin = User::first() ?? User::create([
            'name'     => 'System Administrator',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $memosData = [
            [
                'ref'         => 'FTS-MEMO-2026-001',
                'title'       => 'On-Site HSE & Personal Protective Equipment (PPE) Compliance Directive',
                'category'    => 'safety',
                'file_name'   => 'FTS-HSE-Policy-Directive-2026.pdf',
                'file_path'   => 'memos/sample_company_safety_memo_2026.pdf',
                'description' => "Mandatory operational directive for all project engineers, site foremen, and field technicians regarding Level-3 PPE enforcement, daily toolbox talks, and hot-work safety permits.\n\nAll assigned staff must read the attached document and submit their digital acknowledgment.",
                'subject'     => 'MANDATORY ON-SITE HSE COMPLIANCE & PERSONAL PROTECTIVE EQUIPMENT (PPE) REGULATIONS',
                'author'      => 'Executive Management / HSE Directorate',
                'recipient'   => 'All Project Engineers, Site Foremen & Operational Personnel',
                'signer_left' => "Eng. Tariq Mansoor<br>Director of Operations & HSE",
                'signer_right'=> "Hassan Al-Zahrani<br>General Manager, Fire Technical Services",
                'section1'    => '1. Purpose & Executive Directive',
                'content1'    => 'In alignment with updated UAE Ministry of Human Resources and Emiratisation (MOHRE) health and safety standards, this memorandum establishes mandatory operational protocols across all active project sites, maintenance contracts, and workshop facilities operated by Fire Technical Services.',
                'section2'    => '2. Key Operational Directives',
                'items'       => [
                    'Mandatory Level-3 PPE: Standard safety helmets, high-visibility reflective vests, steel-toe footwear, and protective eyewear are strictly required upon entering any designated active work zone.',
                    'Pre-Shift Safety Briefings (Toolbox Talks): Site Supervisors must conduct a mandatory 10-minute briefing prior to daily shift deployment, specifically logging attendance into the FTS Portal.',
                    'Emergency Hot-Work Permits: Welding, cutting, and grinding operations require a formal Hot-Work Permit countersigned by the site HSE officer before commencement.',
                    'Incident Reporting Protocol: Any near-miss or equipment malfunction must be reported within 60 minutes via the portal reporting system.',
                ],
                'notice'      => 'LEGAL NOTICE & MANDATORY DIGITAL ACKNOWLEDGMENT: Receipt and comprehension of this memorandum is legally binding on all assigned staff members. Every staff member must log into the FTS Portal and submit their formal digital acknowledgment by checking the confirmation box below the memo.',
            ],
            [
                'ref'         => 'FTS-MEMO-2026-002',
                'title'       => 'Information Security & Remote Access Authentication Protocols',
                'category'    => 'policy',
                'file_name'   => 'FTS-IT-Security-Protocol-2026.pdf',
                'file_path'   => 'memos/fts_memo_2026_002_it_security.pdf',
                'description' => "Corporate cybersecurity directive establishing mandatory multi-factor authentication (MFA), password rotation, secure VPN protocols for off-site engineers, and data privacy compliance for all portal users.",
                'subject'     => 'INFORMATION SECURITY POLICY: REMOTE ACCESS, MFA AND DATA CONFIDENTIALITY',
                'author'      => 'IT & Cybersecurity Department',
                'recipient'   => 'All FTS Staff, Engineers, and Portal System Users',
                'signer_left' => "Eng. Zaid Kareem<br>Head of IT Infrastructure & Security",
                'signer_right'=> "Hassan Al-Zahrani<br>General Manager, Fire Technical Services",
                'section1'    => '1. Objective & Security Mandate',
                'content1'    => 'To protect proprietary client blueprints, tender quotations, and financial transaction records against unauthorized access, phishing, and ransomware threats, Fire Technical Services has enacted strict cybersecurity standards effective immediately.',
                'section2'    => '2. Mandatory IT Directives',
                'items'       => [
                    'Multi-Factor Authentication (MFA): Mandatory two-step verification must be activated on all company email and portal accounts by the end of the current billing cycle.',
                    'Authorized Devices Only: Off-site access to ERP records and engineering drawings is restricted to registered company laptops and managed mobile devices with encrypted storage.',
                    'Strict Password Hygiene: Passwords must contain a minimum of 12 alphanumeric and special characters, changed every 90 days without repeating previous credentials.',
                    'Zero Data Leakage: Exporting client lists, project drawing CAD files, or vendor pricing to unauthorized external drives or personal cloud accounts is strictly prohibited.',
                ],
                'notice'      => 'CONFIDENTIALITY & IT COMPLIANCE OBLIGATION: All portal users must read and digitally acknowledge this protocol. Non-compliance may lead to temporary suspension of system privileges and internal disciplinary review.',
            ],
            [
                'ref'         => 'FTS-MEMO-2026-003',
                'title'       => 'Annual Performance Appraisal Cycle & Maintenance Shift Scheduling',
                'category'    => 'hr',
                'file_name'   => 'FTS-HR-Scheduling-Review-2026.pdf',
                'file_path'   => 'memos/fts_memo_2026_003_hr_scheduling.pdf',
                'description' => "Human Resources operational circular detailing the Q4 performance appraisal cycle, biometric attendance compliance, revised maintenance shift rosters, and leave application cut-off dates.",
                'subject'     => 'WORKPLACE POLICY: Q4 PERFORMANCE EVALUATION & REVISED SITE SHIFT ROSTER',
                'author'      => 'Human Resources & Personnel Management',
                'recipient'   => 'All Department Heads, Engineers, Technicians & Administrative Staff',
                'signer_left' => "Fatima Al-Nuaimi<br>Director of Human Resources",
                'signer_right'=> "Hassan Al-Zahrani<br>General Manager, Fire Technical Services",
                'section1'    => '1. Overview & Annual Appraisal Cycle',
                'content1'    => 'Fire Technical Services is launching its annual comprehensive performance review process. All staff members are required to coordinate with their direct line supervisors to complete their self-assessments and objective goal reviews for the upcoming operational year.',
                'section2'    => '2. Key Operational Guidelines',
                'items'       => [
                    'Self-Appraisal Deadline: All employees must submit their completed appraisal forms through the HR portal module no later than the 15th of next month.',
                    'Biometric Attendance Tracking: Morning and evening clock-in/out stamps are strictly synchronized with payroll processing. Unjustified manual overwrites will not be permitted.',
                    'Emergency Service Rotations: Facilities maintenance technicians assigned to 24/7 on-call rosters must remain within designated response zones during their scheduled coverage.',
                    'Annual Leave Blackout Periods: Leave requests during major site commissioning dates must be submitted at least 30 days in advance.',
                ],
                'notice'      => 'HR MANDATE & COMPLIANCE: Every employee is required to read this circular carefully and confirm their acknowledgment on the portal. Questions regarding appraisals may be addressed to hr@fts.ae.',
            ],
        ];

        foreach ($memosData as $data) {
            // Build HTML for DomPDF
            $itemsHtml = '';
            foreach ($data['items'] as $item) {
                $itemsHtml .= '<li>' . htmlspecialchars($item) . '</li>';
            }

            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>' . htmlspecialchars($data['title']) . '</title>
                <style>
                    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #222; margin: 30px 40px; font-size: 13px; line-height: 1.6; }
                    .header-table { width: 100%; border-bottom: 3px solid #800000; padding-bottom: 15px; margin-bottom: 25px; }
                    .company-name { font-size: 22px; font-weight: bold; color: #800000; text-transform: uppercase; letter-spacing: 1px; }
                    .company-sub { font-size: 11px; color: #666; }
                    .memo-badge { background: #800000; color: #fff; padding: 6px 14px; font-size: 14px; font-weight: bold; border-radius: 4px; display: inline-block; }
                    .meta-table { width: 100%; margin-bottom: 25px; background: #f9f9f9; border: 1px solid #ddd; padding: 10px; }
                    .meta-table td { padding: 6px 10px; }
                    .meta-label { font-weight: bold; color: #444; width: 15%; }
                    .section-title { font-size: 15px; font-weight: bold; color: #800000; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px; margin-bottom: 10px; }
                    .notice-box { background: #fff8e1; border-left: 4px solid #ffb300; padding: 12px 15px; margin: 20px 0; font-size: 12px; }
                    .footer { margin-top: 45px; border-top: 1px solid #ddd; padding-top: 15px; font-size: 11px; color: #777; text-align: center; }
                    .signature-table { width: 100%; margin-top: 35px; }
                    .sig-box { width: 45%; border-top: 1px solid #333; padding-top: 8px; text-align: center; font-size: 12px; }
                </style>
            </head>
            <body>
                <table class="header-table">
                    <tr>
                        <td>
                            <div class="company-name">FIRE TECHNICAL SERVICES</div>
                            <div class="company-sub">Fire Protection, Engineering Solutions & Facility Maintenance</div>
                        </td>
                        <td align="right">
                            <span class="memo-badge">OFFICIAL CIRCULAR</span>
                        </td>
                    </tr>
                </table>

                <table class="meta-table">
                    <tr>
                        <td class="meta-label">TO:</td>
                        <td>' . htmlspecialchars($data['recipient']) . '</td>
                        <td class="meta-label">DATE:</td>
                        <td>' . date('d F Y') . '</td>
                    </tr>
                    <tr>
                        <td class="meta-label">FROM:</td>
                        <td>' . htmlspecialchars($data['author']) . '</td>
                        <td class="meta-label">REF NO:</td>
                        <td><strong>' . htmlspecialchars($data['ref']) . '</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label">SUBJECT:</td>
                        <td colspan="3"><strong>' . htmlspecialchars($data['subject']) . '</strong></td>
                    </tr>
                </table>

                <div class="section-title">' . htmlspecialchars($data['section1']) . '</div>
                <p>' . htmlspecialchars($data['content1']) . '</p>

                <div class="section-title">' . htmlspecialchars($data['section2']) . '</div>
                <ul>' . $itemsHtml . '</ul>

                <div class="notice-box">
                    <strong>LEGAL NOTICE & MANDATORY DIGITAL ACKNOWLEDGMENT:</strong><br>
                    ' . htmlspecialchars($data['notice']) . '
                </div>

                <table class="signature-table">
                    <tr>
                        <td class="sig-box">
                            ' . $data['signer_left'] . '
                        </td>
                        <td style="width: 10%;"></td>
                        <td class="sig-box">
                            ' . $data['signer_right'] . '
                        </td>
                    </tr>
                </table>

                <div class="footer">
                    FTS Portal Document ID: ' . htmlspecialchars($data['ref']) . ' &bull; Fire Technical Services LLC &bull; Confidential Internal Communication
                </div>
            </body>
            </html>
            ';

            // Generate PDF file
            $pdf = Pdf::loadHTML($html);
            $output = $pdf->output();
            Storage::disk('public')->put($data['file_path'], $output);

            // Copy to public/storage
            File::put($publicDir . '/' . basename($data['file_path']), $output);

            $fileSize = Storage::disk('public')->size($data['file_path']);

            $memo = Memo::updateOrCreate(
                ['reference_number' => $data['ref']],
                [
                    'title'            => $data['title'],
                    'description'      => $data['description'],
                    'category'         => $data['category'],
                    'file_path'        => $data['file_path'],
                    'file_name'        => $data['file_name'],
                    'file_type'        => 'pdf',
                    'file_size'        => $fileSize,
                    'uploaded_by'      => $admin->id,
                    'recipient_type'   => 'all',
                    'recipient_ids'    => null,
                    'memo_date'        => now()->toDateString(),
                    'expires_at'       => now()->addMonths(6),
                    'status'           => 'published',
                    'published_at'     => now(),
                ]
            );
        }

        // Keep 1 acknowledgment on Memo 1 for testing the "Already Acknowledged" state
        $memo1 = Memo::where('reference_number', 'FTS-MEMO-2026-001')->first();
        if ($memo1) {
            MemoAcknowledgment::updateOrCreate(
                ['memo_id' => $memo1->id, 'user_id' => $admin->id],
                [
                    'acknowledged_at'        => now(),
                    'ip_address'             => '127.0.0.1',
                    'user_agent'             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) FTS Portal Client',
                    'confirmation_statement' => 'I confirm that I have read and carefully understood this memo.',
                ]
            );
        }
    }
}
