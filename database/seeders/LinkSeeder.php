<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\LocalUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Link::insert([
            [
                'uuid' => 'saCNpipzPt9F',
                'image' => 'saCNpipzPt9F.svg',
                'is_sold' => 0,
                'batch_id' => 1,
                'local_user_id' => 1,
            ],
            [
                'uuid' => 'AFYoQuawCXwp',
                'image' => 'AFYoQuawCXwp.svg',
                'is_sold' => 0,
                'batch_id' => 1,
                'local_user_id' => null,
            ],
            [
                'uuid' => 'I3undqwefDu6',
                'image' => 'I3undqwefDu6.svg',
                'is_sold' => 0,
                'batch_id' => 1,
                'local_user_id' => null,
            ],
            [
                'uuid' => 'brHGNhSESk77',
                'image' => 'brHGNhSESk77.svg',
                'is_sold' => 0,
                'batch_id' => 1,
                'local_user_id' => null,
            ],
            [
                'uuid' => 'T903UFxuCtj5',
                'image' => 'T903UFxuCtj5.svg',
                'is_sold' => 0,
                'batch_id' => 1,
                'local_user_id' => null,
            ],
        ]);
    }
}
