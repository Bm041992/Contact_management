<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Contact extends Model
{
    protected $fillable = [
        'name', 'gender', 'profile_image', 'additional_file', 'is_active'
    ];

    public function emails()
    {
        return $this->hasMany(ContactEmail::class);
    }

    public function phones()
    {
        return $this->hasMany(ContactPhone::class);
    }
   
    public function custom_field_values()
    {
        return $this->hasMany(ContactCustomFieldValue::class, 'contact_id', 'id');
    }

    protected static function booted()
    {
        static::deleting(function ($contact) {
            // Delete related emails
            $contact->emails()->delete();

            // Delete related phones
            $contact->phones()->delete();

            // Delete related custom field values
            $contact->custom_field_values()->delete();
        });
        static::deleted(function ($contact) {
            if (!empty($contact->profile_image) && Storage::disk('public')->exists($contact->profile_image)) {
                Storage::disk('public')->delete($contact->profile_image);
            }

            if (!empty($contact->additional_file) && Storage::disk('public')->exists($contact->additional_file)) {
                Storage::disk('public')->delete($contact->additional_file);
            }

        });
    }
}
