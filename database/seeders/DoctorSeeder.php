<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'first_name' => 'Alice',
                'last_name' => 'Reyes',
                'specialty' => 'General Medicine',
                'availability' => 'Mon-Fri 09:00-17:00',
                'current_patients' => 0,
                'max_patients_per_day' => 25,
                'expertise' => 'Primary care, chronic disease management',
                'created_at' => now(),
            ],
            [
                'first_name' => 'Benjamin',
                'last_name' => 'Lopez',
                'specialty' => 'Pediatrics',
                'availability' => 'Mon-Thu 08:00-16:00',
                'current_patients' => 0,
                'max_patients_per_day' => 20,
                'expertise' => 'Child wellness, vaccinations',
                'created_at' => now(),
            ],
            [
                'first_name' => 'Carmen',
                'last_name' => 'Dela Cruz',
                'specialty' => 'Emergency Medicine',
                'availability' => 'Rotating shifts',
                'current_patients' => 0,
                'max_patients_per_day' => 40,
                'expertise' => 'Trauma, acute care',
                'created_at' => now(),
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'Garcia',
                'specialty' => 'Internal Medicine',
                'availability' => 'Tue-Fri 10:00-18:00',
                'current_patients' => 0,
                'max_patients_per_day' => 22,
                'expertise' => 'Diabetes, hypertension, adult care',
                'created_at' => now(),
            ],
        ];

        foreach ($doctors as $data) {
            Doctor::create($data);
        }
    }
}
