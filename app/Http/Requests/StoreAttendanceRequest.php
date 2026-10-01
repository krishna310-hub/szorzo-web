<?php
namespace App\Http\Requests;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreAttendanceRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->can('create', Attendance::class) ?? false; }
    public function rules(): array
    {
        return [
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'records.*.user_id' => ['nullable', 'integer'],
            'records.*.status' => ['required', Rule::in(Attendance::STATUSES)],
            'records.*.check_in' => ['nullable', 'date_format:H:i'],
            'records.*.check_out' => ['nullable', 'date_format:H:i', 'after:records.*.check_in'],
            'records.*.remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
