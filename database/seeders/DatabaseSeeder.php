<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // populate tags
        $tags = ['cursada', 'parcial', 'final', 'otro'];
        foreach ($tags as $tagName) {
            Tag::firstOrCreate(['tag' => $tagName]);
        }
        $modes = ['online', 'presencial', 'hibrido'];
        foreach ($modes as $modeName) {
            Tag::firstOrCreate(['tag' => $modeName]);
        }

        $facultades = ['EXACTAS', 'ECONOMICAS', 'HUMANAS'];
        foreach ($facultades as $facultad) {
            Facultad::firstOrCreate(['name' => $facultad]);
        }
        $carreras = ['carrera1', 'carrera2', 'carrera3', 'carrera4', 'carrera5', 'carrera6', 'carrera7', 'carrera8', 'carrera9', 'carrera10'];
        foreach ($carreras as $carrera) {
            $random_facultad = $facultades[array_rand($facultades)];
            $_facultad = Facultad::where('name', $random_facultad)->first();
            Carrera::firstOrCreate(['id_facultad' => $_facultad->id, 'name' => $carrera]);
        }

        $users = \App\Models\User::factory(15)->create(); // generate fake users
        $groups = \App\Models\Group::factory(20)->create();  // generate fake groups

        // Populate the user_group pivot table
        foreach ($groups as $group) {

            // add members to groups
            $group_owner_id = $group->owner_id;
            $group_max_members = $group->max_members == 0 ? 5 : $group->max_members; // to not exceed max members
            $groupMembersIDs = $users->reject(function ($user) use ($group_owner_id) { return $user->id == $group_owner_id; })->random(rand(1, $group_max_members))->pluck('id')->toArray();
            $group->users()->attach($groupMembersIDs);

            $random_carrera = $carreras[array_rand($carreras)];
            // $group->id_carrera = Carrera::where('name', $random_carrera)->first();

            // add tags to group
            $randomTag = $tags[array_rand($tags)];
            $tag = Tag::where('tag', $randomTag)->first();
            $group->tags()->attach($tag);
            $randomMode = $modes[array_rand($modes)];
            $mode = Tag::where('tag', $randomMode)->first();
            $group->tags()->attach($mode);

        }

    }
}
