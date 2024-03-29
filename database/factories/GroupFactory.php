<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Carrera;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $owner_id = $this->faker->randomElement(User::pluck('id')->toArray());
        $privacy = $this->faker->randomElement(['open', 'closed', 'private']);
        $id_carrera = $this->faker->randomElement(Carrera::pluck('id')->toArray());

        if ($this->faker->biasedNumberBetween(0,100, 'sqrt') > 50){
            $max_members = NULL;
        }else{
            $max_members = $this->faker->numberBetween(0,10);
        }

        return [
            'ulid' => $this->faker->md5(),
            'owner_id' => $owner_id,
            'title' => $this->faker->sentence(),
            'id_carrera' => $id_carrera,
            'description' => $this->faker->paragraph(),
            'privacy' => $privacy,
            'max_members' => $max_members,
        ];

    }
}
