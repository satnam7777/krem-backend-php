<?php

namespace App\Http\Requests\Schedule;

use App\Models\StaffSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StaffScheduleUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'staff_id' => ['required','integer','exists:staff,id'],
            // Either weekly recurring window or date-specific override
            'weekday' => ['nullable','integer','min:0','max:6'],
            'date' => ['nullable','date'],
            'start_time' => ['required','date_format:H:i'],
            'end_time' => ['required','date_format:H:i','after:start_time'],
            'is_available' => ['required','boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $salon = $this->attributes->get('currentSalon');
            if (!$salon) return;

            $data = $this->validated();
            $staffId = (int)$data['staff_id'];

            $weekday = $data['weekday'] ?? null;
            $date = $data['date'] ?? null;

            if (!$weekday && !$date) {
                $v->errors()->add('weekday', 'Either weekday or date must be provided.');
                return;
            }

            // prevent overlaps for same (salon, staff, weekday/date)
            $q = StaffSchedule::query()
                ->where('salon_id',$salon->id)
                ->where('staff_id',$staffId);

            if ($weekday !== null) $q->where('weekday',(int)$weekday)->whereNull('date');
            if ($date !== null) $q->whereDate('date',$date);

            // exclude current record on update
            if ($this->route('id')) $q->where('id','!=',(int)$this->route('id'));
            if ($this->route('schedule')) $q->where('id','!=',(int)$this->route('schedule'));
            if ($this->route('staff_schedule')) $q->where('id','!=',(int)$this->route('staff_schedule'));

            $start = $data['start_time'];
            $end = $data['end_time'];

            $overlap = $q->where(function($qq) use ($start,$end) {
                $qq->whereBetween('start_time', [$start, $end])
                   ->orWhereBetween('end_time', [$start, $end])
                   ->orWhere(function($q3) use ($start,$end){
                        $q3->where('start_time','<=',$start)->where('end_time','>=',$end);
                   });
            })->exists();

            if ($overlap) {
                $v->errors()->add('start_time', 'Schedule overlaps with an existing window for this staff member.');
            }
        });
    }
}
