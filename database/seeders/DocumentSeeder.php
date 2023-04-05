<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        Document::create([
            'post_document' => 'Validasi'
        ]);
        Document::create([
            'post_document' => 'Flowchart'
        ]);
        Document::create([
            'post_document' => 'HACCP'
        ]);
        Document::create([
            'post_document' => 'SOP'
        ]);
        Document::create([
            'post_document' => 'Instruksi Kerja'
        ]);
        Document::create([
            'post_document' => 'Ketentuan'
        ]);
        Document::create([
            'post_document' => 'Form'
        ]);
    }
}
