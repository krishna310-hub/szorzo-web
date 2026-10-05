<?php
namespace App\Http\Requests;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreAttendanceRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->can('create', Attendance::class) ?? false; }
    public function rules(): array { return ['attendance_date' => ['required','date_format:Y-m-d'], 'records' => ['required','array','min:1'], 'records.*.user_id' => ['required','integer',Rule::exists('users','id')->where(fn($q) => $q->whereNotIn('role_id',fn($s) => $s->select('id')->from('roles')->where('access_level','super_admin')))], 'records.*.status' => ['required',Rule::in(Attendance::STATUSES)], 'records.*.check_in' => ['nullable','date_format:H:i'], 'records.*.check_out' => ['nullable','date_format:H:i','after:records.*.check_in'], 'records.*.remarks' => ['nullable','string','max:1000']]; }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $date = $this->input('attendance_date');
            if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return;

            $ids = collect($this->input('records', []))->pluck('user_id')->filter()->unique();
            $eligible = \App\Models\User::employedOn($date)->whereIn('users.id', $ids)->pluck('users.id')->map(fn ($id) => (string) $id)->all();
            foreach ($this->input('records', []) as $index => $record) {
                if (isset($record['user_id']) && ! in_array((string) $record['user_id'], $eligible, true)) {
                    $validator->errors()->add("records.{$index}.user_id", 'The selected user was not employed on the attendance date.');
                }
            }
        });
    }
}
