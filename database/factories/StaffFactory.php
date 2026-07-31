namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fio' => fake()->name(),
            'phone_number' => fake()->phoneNumber(),
            'organization_id' => rand(1, 5),
            'position' => fake()->jobTitle(),
            'status' => fake()->randomElement(['Active', 'Inactive']),
        ];
    }
}
