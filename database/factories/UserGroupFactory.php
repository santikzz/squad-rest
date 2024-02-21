<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Group;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class UserGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = $this->faker->randomElement(User::pluck('id')->toArray());
        $groupId = $this->faker->randomElement(Group::pluck('id')->toArray());

        return [
            'user_id' => $userId,
            'group_id' => $groupId,
        ];
    }
}
