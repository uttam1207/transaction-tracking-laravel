<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_title',
        'category',
        'document_number',
        'issue_date',
        'expiry_date',
        'file_path',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];
}
