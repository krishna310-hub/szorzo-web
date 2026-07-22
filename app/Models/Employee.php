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
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
