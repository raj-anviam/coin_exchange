<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function intermediateAddrersses() {
        return $this->hasMany(IntermediateAddress::class);
    }
    
}
