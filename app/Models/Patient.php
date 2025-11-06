<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Patient extends Model
{
    protected $table = 'patients';
    public $timestamps = false;

    protected $fillable = [
        'first_name','last_name','age','gender','contact','email','symptoms',
        'priority','service_type','status','check_in_time','completion_time','assigned_doctor_id'
    ];

    // helper accessor
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Assigned doctor relation (optional)
     */
    public function assignedDoctor()
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }
}