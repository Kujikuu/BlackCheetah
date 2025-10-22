<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProvincesController extends Controller
{
    /**
     * Get list of Saudi Arabian provinces with their cities
     */
    public function index(): JsonResponse
    {
        try {
            // Cache for 7 days
            $provinces = Cache::remember('saudi_provinces_cities', 60 * 24 * 7, function () {
                return $this->getProvincesData();
            });

            return $this->successResponse($provinces, 'Provinces retrieved successfully');
        } catch (\Exception $e) {
            // Return fallback list on any error
            return $this->successResponse($this->getProvincesData(), 'Provinces retrieved successfully');
        }
    }

    /**
     * Get Saudi Arabian provinces with their cities
     */
    private function getProvincesData(): array
    {
        return [
            [
                'name' => 'Riyadh',
                'cities' => ['Riyadh', 'Al Kharj', 'Al Majma\'ah', 'Al Quway\'iyah', 'Ad Dilam', 'Al Ghat'],
            ],
            [
                'name' => 'Makkah',
                'cities' => ['Mecca', 'Jeddah', 'Taif', 'Rabigh', 'Khulais', 'Rania', 'Turubah', 'Al Jumum', 'Al Kamil'],
            ],
            [
                'name' => 'Madinah',
                'cities' => ['Medina', 'Yanbu', 'Al-Ula', 'Badr', 'Khaybar', 'Al Mahd'],
            ],
            [
                'name' => 'Eastern Province',
                'cities' => ['Dammam', 'Al Khobar', 'Dhahran', 'Jubail', 'Al Ahsa', 'Qatif', 'Hafr Al-Batin', 'Ras Tanura'],
            ],
            [
                'name' => 'Qassim',
                'cities' => ['Buraydah', 'Unaizah', 'Ar Rass', 'Al Mithnab', 'Al Badaya', 'Riyadh Al Khabra'],
            ],
            [
                'name' => 'Asir',
                'cities' => ['Abha', 'Khamis Mushait', 'Bisha', 'Ahad Rafidah', 'An Namas', 'Sarat Abidah', 'Bariq'],
            ],
            [
                'name' => 'Tabuk',
                'cities' => ['Tabuk', 'Duba', 'Umluj', 'Tayma', 'Al Wajh', 'Haql'],
            ],
            [
                'name' => 'Hail',
                'cities' => ['Hail', 'Baqaa', 'Al Ghazalah', 'Al Shamli', 'Mawqaq'],
            ],
            [
                'name' => 'Northern Borders',
                'cities' => ['Arar', 'Rafha', 'Turaif', 'Al Uwayqilah'],
            ],
            [
                'name' => 'Jazan',
                'cities' => ['Jazan', 'Sabya', 'Abu Arish', 'Samtah', 'Ahad Al Masarihah', 'Baish', 'Ad Darb'],
            ],
            [
                'name' => 'Najran',
                'cities' => ['Najran', 'Sharurah', 'Habuna', 'Badr Al Janoub', 'Yadamah'],
            ],
            [
                'name' => 'Al Bahah',
                'cities' => ['Al Bahah', 'Baljurashi', 'Al Mandaq', 'Al Mikhwah', 'Al Qura', 'Al Aqiq'],
            ],
            [
                'name' => 'Al Jawf',
                'cities' => ['Sakakah', 'Dumat Al-Jandal', 'Qurayyat', 'Tabarjal'],
            ],
        ];
    }
}

