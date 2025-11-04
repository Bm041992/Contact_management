<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = ['name', 'field_type', 'is_required'];

    public function custom_field_values()
    {
        return $this->hasMany(ContactCustomFieldValue::class, 'custom_field_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($customField) {
            // When a custom field is deleted, delete all related values
            $customField->custom_field_values()->delete();
        });
    }
}
