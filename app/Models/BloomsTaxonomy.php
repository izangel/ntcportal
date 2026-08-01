<?php
// app/Models/BloomsTaxonomy.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloomsTaxonomy extends Model
{
    protected $fillable = ['domain', 'code', 'level', 'action_verbs'];

    public function clos(): HasMany
    {
        return $this->hasMany(CourseLearningOutcome::class);
    }
}