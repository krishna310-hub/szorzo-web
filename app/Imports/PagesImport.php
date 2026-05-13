<?php

namespace App\Imports;

use App\Models\Pages;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class PagesImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // $rows->shift();
        $lastId = Pages::max('id') ?? 0;

        foreach ($rows as $index => $row) {

            if (empty(trim($row['location'] ?? '')) &&
                empty(trim($row['category'] ?? ''))
            ) {
                continue;
            }
            $urlSlug = $row['url']
                ? Str::slug($row['url'])
                : Str::slug($row['name'] ?? 'page');

            $faqs = null;

            if (!empty($row['faq_heading']) || !empty($row['faq_content'])) {
                $faqs = json_encode([
                    [
                        'question' => trim($row['faq_heading'] ?? ''),
                        'answer'   => trim($row['faq_content'] ?? ''),
                    ]
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            Pages::updateOrCreate(
                ['url_slug' => $urlSlug],
                [
                    'location'         => $row['location'] ?? null,
                    'category'         => $row['category'] ?? null,
                    'name'             => $row['name'] ?? null,
                    'image'            => $row['banner_link'] ?? null,
                    'url_slug'         => $urlSlug,
                    'banner_content'   => $row['banner_content'] ?? null,
                    'page_content'     => $row['page_content'] ?? null,
                    'faqs'             => $faqs,
                    'meta_title'       => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_content'] ?? null,
                    'meta_keyword'     => $row['meta_keyword'] ?? null,
                    'status'           => 1,
                    'page_code'        => '#GD' . ($lastId + $index + 1),
                ]
            );
        }
    }

}
