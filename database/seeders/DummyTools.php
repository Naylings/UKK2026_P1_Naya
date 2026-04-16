<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyTools extends Seeder
{
    public function run(): void
    {
        // ─── Categories ─────────────────────────────
        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Elektronik',
                'description' => 'Peralatan elektronik'
            ],
        ]);

        // ─── Tools ─────────────────────────────
        DB::table('tools')->insert([
            // SINGLE
            [
                'id' => 1,
                'category_id' => 1,
                'name' => 'Laptop AXB',
                'item_type' => 'single',
                'price' => 10000000,
                'min_credit_score' => 50,
                'description' => 'Laptop single',
                'code_slug' => 'AXB',
                'photo_path' => 'tools/laptop.jpg',
                'created_at' => now(),
            ],

            // BUNDLE
            [
                'id' => 2,
                'category_id' => 1,
                'name' => 'Paket AXB',
                'item_type' => 'bundle',
                'price' => 15000000,
                'min_credit_score' => 60,
                'description' => 'Bundle alat AXB',
                'code_slug' => 'SET-AXB',
                'photo_path' => 'tools/bundle.jpg',
                'created_at' => now(),
            ],

            // BUNDLE TOOL / COMPONENT
            [
                'id' => 3,
                'category_id' => 1,
                'name' => 'Komponen AXB',
                'item_type' => 'bundle_tool',
                'price' => 3000000,
                'min_credit_score' => null,
                'description' => 'Sub tool dari bundle',
                'code_slug' => 'SUB-AXB',
                'photo_path' => 'tools/component.jpg',
                'created_at' => now(),
            ],
        ]);

        // ─── Bundle Relation ─────────────────────────────
        DB::table('bundle_tools')->insert([
            [
                'id' => 1,
                'bundle_id' => 2, // SET-AXB
                'tool_id' => 3,   // SUB-AXB
                'qty' => 2,
            ],
        ]);

        // ─── Tool Units ─────────────────────────────
        DB::table('tool_units')->insert([
            // Single
            [
                'code' => 'AXB-001',
                'tool_id' => 1,
                'status' => 'available',
                'notes' => 'Unit laptop',
                'created_at' => now(),
            ],

            // Bundle Tool (component punya unit sendiri)
            [
                'code' => 'SUB-AXB-001',
                'tool_id' => 3,
                'status' => 'available',
                'notes' => 'Komponen bundle',
                'created_at' => now(),
            ],
        ]);

        // ─── Unit Conditions (1x awal saja) ─────────────────────────────
        DB::table('unit_conditions')->insert([
            [
                'id' => 'UC-AXB-001',
                'unit_code' => 'AXB-001',
                'return_id' => null,
                'conditions' => 'good',
                'notes' => 'Kondisi awal',
                'recorded_at' => now(),
            ],
            [
                'id' => 'UC-SUB-AXB-001',
                'unit_code' => 'SUB-AXB-001',
                'return_id' => null,
                'conditions' => 'good',
                'notes' => 'Kondisi awal',
                'recorded_at' => now(),
            ],
        ]);
    }
}