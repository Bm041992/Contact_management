<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactCustomFieldValue extends Model
{
    protected $fillable = [
        'contact_id',
        'custom_field_id',
        'value',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function custom_field()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id', 'id');
    }
}
