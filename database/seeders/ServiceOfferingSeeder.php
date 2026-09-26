<?php

namespace Database\Seeders;

use App\Services\BusinessKnowledgeBase;
use Illuminate\Database\Seeder;

class ServiceOfferingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(BusinessKnowledgeBase $knowledgeBase): void
    {
        $knowledgeBase->installDefaultItOfferings();
    }
}
