<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'doctors';
    public $timestamps = false;

    protected $fillable = [
        'first_name','last_name','specialty','availability',
        'current_patients','max_patients_per_day','expertise','created_at'
    ];

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}