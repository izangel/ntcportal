<?php

// database/seeders/BloomsTaxonomySeeder.php
namespace Database\Seeders;

use App\Models\BloomsTaxonomy;
use Illuminate\Database\Seeder;

class BloomsTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            // Cognitive Domain
            ['domain' => 'Cognitive', 'code' => 'C1', 'level' => 'Remembering', 'action_verbs' => 'Define, List, State, Identify, Recall'],
            ['domain' => 'Cognitive', 'code' => 'C2', 'level' => 'Understanding', 'action_verbs' => 'Explain, Describe, Discuss, Summarize'],
            ['domain' => 'Cognitive', 'code' => 'C3', 'level' => 'Applying', 'action_verbs' => 'Apply, Demonstrate, Solve, Implement, Use'],
            ['domain' => 'Cognitive', 'code' => 'C4', 'level' => 'Analyzing', 'action_verbs' => 'Analyze, Differentiate, Examine, Contrast'],
            ['domain' => 'Cognitive', 'code' => 'C5', 'level' => 'Evaluating', 'action_verbs' => 'Evaluate, Judge, Critique, Justify, Assess'],
            ['domain' => 'Cognitive', 'code' => 'C6', 'level' => 'Creating', 'action_verbs' => 'Design, Construct, Develop, Formulate, Plan'],
            
            // Psychomotor Domain
            ['domain' => 'Psychomotor', 'code' => 'P1', 'level' => 'Perception / Imitation', 'action_verbs' => 'Copy, Follow, Repeat, Replicate'],
            ['domain' => 'Psychomotor', 'code' => 'P2', 'level' => 'Manipulation / Precision', 'action_verbs' => 'Execute, Perform, Demonstrate accurately'],
            
            // Affective Domain
            ['domain' => 'Affective', 'code' => 'A1', 'level' => 'Receiving / Responding', 'action_verbs' => 'Acknowledge, Comply, Follow, Participate'],
            ['domain' => 'Affective', 'code' => 'A2', 'level' => 'Valuing / Internalizing', 'action_verbs' => 'Advocate, Formulate, Commit, Display leadership'],
        ];

        foreach ($levels as $item) {
            BloomsTaxonomy::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}