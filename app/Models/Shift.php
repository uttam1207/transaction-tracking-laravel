<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['key', 'name', 'start_time', 'end_time', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function toShiftArray(): array
    {
        return [
            'type'  => $this->key,
            'label' => $this->name,
            'start' => $this->start_time ? substr($this->start_time, 0, 5) : null,
            'end'   => $this->end_time   ? substr($this->end_time,   0, 5) : null,
        ];
    }
}
