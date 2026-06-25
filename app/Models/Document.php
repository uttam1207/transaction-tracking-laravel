<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Document extends Model
{
    protected $fillable = [
        'title', 'description', 'file_path', 'file_name',
        'file_type', 'file_size', 'category', 'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string
    {
        return match(true) {
            str_contains($this->file_type, 'pdf')   => 'bi-file-earmark-pdf text-danger',
            str_contains($this->file_type, 'word')  => 'bi-file-earmark-word text-primary',
            str_contains($this->file_type, 'sheet') => 'bi-file-earmark-spreadsheet text-success',
            str_contains($this->file_type, 'image') => 'bi-file-earmark-image text-warning',
            str_contains($this->file_type, 'zip')   => 'bi-file-earmark-zip text-secondary',
            default                                  => 'bi-file-earmark text-muted',
        };
    }
}
