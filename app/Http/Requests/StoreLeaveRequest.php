<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        if ($user->isInvestor() || (int) $user->role_id === 6) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->isInternalEmployee() || $user->can('create', LeaveRequest::class);
    }

    public function rules(): array
    {
        return [
            'leave_type' => ['required', Rule::in(LeaveRequest::TYPES)],
            'from_date' => ['required', 'date_format:Y-m-d'],
            'to_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'reason' => ['required', 'string', 'min:5', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type.required' => 'Please select a leave type.',
            'leave_type.in' => 'Selected leave type is invalid.',
            'from_date.required' => 'From date is required.',
            'from_date.date_format' => 'From date format must be YYYY-MM-DD.',
            'to_date.required' => 'To date is required.',
            'to_date.date_format' => 'To date format must be YYYY-MM-DD.',
            'to_date.after_or_equal' => 'To date must be on or after the From date.',
            'reason.required' => 'Please provide a reason for your leave request.',
            'reason.min' => 'Please provide a detailed reason (at least 5 characters).',
            'attachment.max' => 'Attachment file size cannot exceed 5 MB.',
            'attachment.mimes' => 'Attachment must be a PDF or image (JPG, PNG).',
        ];
    }
}
