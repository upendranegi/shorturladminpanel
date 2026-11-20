<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'company_name' => $name,
            'company_code' => strtoupper(fake()->unique()->bothify('COMP####')),
            'is_enabled' => 'Y',
            'is_deleted' => 'N',
        ];
    }
}
