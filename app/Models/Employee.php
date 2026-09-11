<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_name',
        'employee_image',
        'dob',
        'gender',
        'marital_status',
        'nationality',
        'blood_group',
        'employee_no',
        'designation',
        'date_of_joining',
        'client_id',
        'mode_id',
        'contract_from_date',
        'contract_to_date',
        'offer_letter',
        'intent_letter',
        'previous_company_offer_letters',
        'relieving_letters',
        'pay_slips',
        'bank_statements',
        'passbook_cheques',
        'pan_card_file',
        'aadhaar_file',
        'twelfth_marksheet',
        'tenth_marksheet',
        'degree_certificate',
        'employee_uan_pf_number',
        'employee_esi_number',
        'mobile_number',
        'alternate_mobile_number',
        'official_mail',
        'personal_mail',
        'permanent_address',
        'current_residential_address',
        'emergency_contact_name',
        'relationship',
        'emergency_contact_number',
        'emergency_contact_mail',
        'emergency_contact_address',
        'pan_card_number',
        'aadhaar_card_number',
        'passport_number',
        'passport_validity_date',
        'fathers_name',
        'fathers_mobile_number',
        'mothers_name',
        'siblings_name',
        'husband_wife_name',
        'husband_wife_dob',
        'spouse_mobile_number',
        'childrens_name_dob',
        'bank_name',
        'account_holders_name',
        'account_number',
        'branch_ifsc_code',
        'mode_of_salary',
        'bank_uan_pf_number',
        'bank_esi_number',
        'any_health_issue',
        'passion',
        'awards_appreciation',
        'status',
        'monthly_gross',
        'basic_salary',
        'hra',
        'conveyance',
        'medical_allowance',
        'special_allowance',
        'overtime_amount',
        'lta',
        'arrears',
        'pf_deduction',
        'esi_deduction',
        'pt_deduction',
        'income_tax',
        'salary_advance',
        'fines',
        'labour_welfare_fund',
        'other_deductions',
        'salary_remarks',
        'document_checklist',
        'educational_certificates',
    ];

    protected $casts = [
        'status' => 'boolean',
        'monthly_gross' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'conveyance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'lta' => 'decimal:2',
        'arrears' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'esi_deduction' => 'decimal:2',
        'pt_deduction' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'salary_advance' => 'decimal:2',
        'fines' => 'decimal:2',
        'labour_welfare_fund' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'date_of_joining' => 'date',
        'contract_from_date' => 'date',
        'contract_to_date' => 'date',
        'previous_company_offer_letters' => 'array',
        'relieving_letters' => 'array',
        'pay_slips' => 'array',
        'bank_statements' => 'array',
        'passbook_cheques' => 'array',
        'educational_certificates' => 'array',
        'document_checklist' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function mode()
    {
        return $this->belongsTo(Mode::class);
    }

    public function linkedUser(): ?User
    {
        $emails = array_filter([
            mb_strtolower((string) $this->official_mail),
            mb_strtolower((string) $this->personal_mail),
        ]);

        if (empty($emails)) {
            return null;
        }

        return User::where(function ($query) use ($emails) {
            foreach ($emails as $email) {
                $query->orWhereRaw('LOWER(email) = ?', [$email]);
            }
        })->first();
    }

    public function getEmploymentModeAttribute(): string
    {
        $modeObj = $this->relationLoaded('mode') ? $this->getRelation('mode') : null;
        if (!$modeObj) {
            try {
                $modeObj = $this->mode;
            } catch (\Throwable $e) {
                $modeObj = null;
            }
        }
        $raw = strtolower(trim((string) ($modeObj?->mode ?? 'Full Time')));
        if (str_contains($raw, 'c2h') || str_contains($raw, 'hire')) {
            return 'C2H';
        }
        if (str_contains($raw, 'contract')) {
            return 'Contract';
        }
        return 'FTE';
    }

    public function isContract(): bool
    {
        return $this->employment_mode === 'Contract';
    }

    public function isC2H(): bool
    {
        return $this->employment_mode === 'C2H';
    }

    public function isFTE(): bool
    {
        return $this->employment_mode === 'FTE';
    }

    public const CHECKLIST_ITEMS = [
        'previous_company_offer_letters' => [
            'key' => 'previous_company_offer_letters',
            'number' => 1,
            'label' => "Previous company's offer letter",
            'type' => 'document_array',
            'field' => 'previous_company_offer_letters',
        ],
        'relieving_letters' => [
            'key' => 'relieving_letters',
            'number' => 2,
            'label' => 'Relieving Letter',
            'type' => 'document_array',
            'field' => 'relieving_letters',
        ],
        'pay_slips' => [
            'key' => 'pay_slips',
            'number' => 3,
            'label' => '3 months’ pay slips',
            'type' => 'document_array',
            'field' => 'pay_slips',
        ],
        'bank_statements' => [
            'key' => 'bank_statements',
            'number' => 4,
            'label' => 'Bank Statements for the past 3 months',
            'type' => 'document_array',
            'field' => 'bank_statements',
        ],
        'educational_certificates' => [
            'key' => 'educational_certificates',
            'number' => 5,
            'label' => 'All Educational certificates',
            'type' => 'education',
            'fields' => ['tenth_marksheet', 'twelfth_marksheet', 'degree_certificate', 'educational_certificates'],
        ],
        'pan_card' => [
            'key' => 'pan_card',
            'number' => 6,
            'label' => 'Pan Card copy',
            'type' => 'document_single',
            'field' => 'pan_card_file',
            'number_field' => 'pan_card_number',
        ],
        'aadhaar_card' => [
            'key' => 'aadhaar_card',
            'number' => 7,
            'label' => 'Adhaar Card copy',
            'type' => 'document_single',
            'field' => 'aadhaar_file',
            'number_field' => 'aadhaar_card_number',
        ],
        'photograph' => [
            'key' => 'photograph',
            'number' => 8,
            'label' => 'Passport size photograph',
            'type' => 'image',
            'field' => 'employee_image',
        ],
        'passbook_cheques' => [
            'key' => 'passbook_cheques',
            'number' => 9,
            'label' => 'Passbook FrontPage / Cancelled cheque (Photocopy)',
            'type' => 'document_array',
            'field' => 'passbook_cheques',
        ],
        'personal_details' => [
            'key' => 'personal_details',
            'number' => 10,
            'label' => 'Personal & Profile Details',
            'type' => 'profile_info',
            'fields' => ['employee_name', 'dob', 'gender', 'mobile_number', 'personal_mail', 'permanent_address'],
        ],
    ];

    public function safeAssetUrl(string $path): string
    {
        try {
            if (function_exists('app') && app()->bound('url')) {
                return asset($path);
            }
        } catch (\Throwable $e) {
            // fallback when application container is not booted
        }
        return '/' . ltrim($path, '/');
    }

    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->employee_image)) {
            $imagePath = 'uploads/employees/' . $this->employee_image;
            try {
                if (function_exists('public_path') && file_exists(public_path($imagePath))) {
                    return $this->safeAssetUrl($imagePath);
                }
            } catch (\Throwable $e) {
                return '/' . $imagePath;
            }
        }
        return $this->safeAssetUrl('admin/images/users/user-dummy-img.jpg');
    }

    public function getProfileCompletionAttribute(): array
    {
        $checklistRaw = is_array($this->document_checklist)
            ? $this->document_checklist
            : (json_decode((string) ($this->document_checklist ?? '[]'), true) ?: []);

        $hasManualChecklist = !empty($checklistRaw);
        $items = [];
        $verifiedCount = 0;
        $uploadedCount = 0;
        $totalCount = count(self::CHECKLIST_ITEMS);

        foreach (self::CHECKLIST_ITEMS as $key => $config) {
            $files = [];
            $isUploaded = false;

            switch ($config['type']) {
                case 'document_array':
                    $docArray = is_array($this->{$config['field']})
                        ? $this->{$config['field']}
                        : (json_decode((string) ($this->{$config['field']} ?? '[]'), true) ?: []);
                    if (!empty($docArray)) {
                        $isUploaded = true;
                        foreach ($docArray as $f) {
                            $files[] = [
                                'name' => $f,
                                'url' => $this->safeAssetUrl('uploads/employees/documents/' . $f),
                            ];
                        }
                    }
                    break;

                case 'document_single':
                    $val = $this->{$config['field']};
                    if (!empty($val)) {
                        $isUploaded = true;
                        $files[] = [
                            'name' => $val,
                            'url' => $this->safeAssetUrl('uploads/employees/documents/' . $val),
                        ];
                    }
                    break;

                case 'image':
                    $val = $this->{$config['field']};
                    if (!empty($val)) {
                        $isUploaded = true;
                        $files[] = [
                            'name' => $val,
                            'url' => $this->avatar_url,
                        ];
                    }
                    break;

                case 'education':
                    foreach (['tenth_marksheet', 'twelfth_marksheet', 'degree_certificate'] as $f) {
                        if (!empty($this->{$f})) {
                            $isUploaded = true;
                            $files[] = [
                                'name' => $this->{$f},
                                'label' => ucwords(str_replace('_', ' ', $f)),
                                'url' => $this->safeAssetUrl('uploads/employees/documents/' . $this->{$f}),
                            ];
                        }
                    }
                    $extraEdu = is_array($this->educational_certificates)
                        ? $this->educational_certificates
                        : (json_decode((string) ($this->educational_certificates ?? '[]'), true) ?: []);
                    if (!empty($extraEdu)) {
                        $isUploaded = true;
                        foreach ($extraEdu as $f) {
                            $files[] = [
                                'name' => $f,
                                'label' => 'Additional Certificate',
                                'url' => $this->safeAssetUrl('uploads/employees/documents/' . $f),
                            ];
                        }
                    }
                    break;

                case 'profile_info':
                    $isUploaded = !empty($this->employee_name) && (!empty($this->mobile_number) || !empty($this->personal_mail));
                    break;
            }

            if ($isUploaded) {
                $uploadedCount++;
            }

            $itemChecklist = $checklistRaw[$key] ?? null;
            $isVerified = false;
            $verifiedAt = null;
            $verifiedBy = null;
            $notes = null;

            if (is_array($itemChecklist)) {
                $isVerified = !empty($itemChecklist['verified']);
                $verifiedAt = $itemChecklist['verified_at'] ?? null;
                $verifiedBy = $itemChecklist['verified_by'] ?? null;
                $notes = $itemChecklist['notes'] ?? null;
            } elseif ($itemChecklist === true || $itemChecklist === 1 || $itemChecklist === '1') {
                $isVerified = true;
            }

            if ($isVerified) {
                $verifiedCount++;
            }

            // Completed when verified; fallback to uploaded if no manual review record exists yet
            $isCompleted = $hasManualChecklist ? $isVerified : $isUploaded;

            $items[$key] = [
                'key' => $key,
                'number' => $config['number'],
                'label' => $config['label'],
                'type' => $config['type'],
                'is_uploaded' => $isUploaded,
                'files' => $files,
                'is_verified' => $isVerified,
                'verified_at' => $verifiedAt,
                'verified_by' => $verifiedBy,
                'notes' => $notes,
                'is_completed' => $isCompleted,
            ];
        }

        $completedCount = $hasManualChecklist ? $verifiedCount : $uploadedCount;
        $percentage = (int) round(($completedCount / $totalCount) * 100);

        return [
            'completed_count' => $completedCount,
            'total_count' => $totalCount,
            'percentage' => $percentage,
            'verified_count' => $verifiedCount,
            'uploaded_count' => $uploadedCount,
            'ratio_text' => "{$completedCount}/{$totalCount}",
            'is_fully_completed' => $completedCount === $totalCount,
            'items' => $items,
        ];
    }

    public function getProgressColorAttribute(): string
    {
        $percent = $this->profile_completion['percentage'] ?? 0;
        if ($percent >= 80) {
            return '#0ab39c'; // success teal
        }
        if ($percent >= 40) {
            return '#f7b84b'; // warning amber
        }
        return '#f06548'; // danger red
    }
}
