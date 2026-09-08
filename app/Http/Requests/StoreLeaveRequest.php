<?php
namespace App\Http\Requests;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreLeaveRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->can('create', LeaveRequest::class) ?? false; }
    public function rules(): array { return ['leave_type' => ['required',Rule::in(LeaveRequest::TYPES)], 'from_date' => ['required','date_format:Y-m-d'], 'to_date' => ['required','date_format:Y-m-d','after_or_equal:from_date'], 'reason' => ['required','string','max:3000'], 'attachment' => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:5120']]; }
}
