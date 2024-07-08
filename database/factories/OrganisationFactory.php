<?php
// database\factories\OrganisationFactory.php
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    public function definition()
    {
        return [
            'orgId' => $this->faker->uuid,
            'name' => $this->faker->company,
            'description' => $this->faker->sentence,
            'userId' => User::factory(),
        ];
    }
}

