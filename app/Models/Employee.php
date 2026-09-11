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
}
