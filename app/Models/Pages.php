<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
    protected $fillable = [
        'location',
        'category',
        'name',
        'image',
        'url_slug',
        'banner_content',
        'page_content',
        'faqs',
        'meta_title',
        'meta_description',
        'meta_keyword',
        'status',
        'page_code'
    ];

    protected $casts = [
        'faqs' => 'array',
        'status' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($page) {
            if (!$page->page_code) {
                $page->update([
                    'page_code' => '#GD' . $page->id
                ]);
            }
        });
    }

}
