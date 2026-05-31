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
        $headers = $rows->first()->toArray();

        $rows = $rows->skip(1);

        $lastId = Pages::max('id') ?? 0;

        foreach ($rows as $index => $row) {

            $row = array_combine($headers, $row->toArray());

            if (
                empty(trim($row['Location'] ?? '')) &&
                empty(trim($row['Category'] ?? '')) &&
                empty(trim($row['Name'] ?? ''))
            ) {
                continue;
            }

            $urlSlug = !empty($row['URL'])
                ? Str::slug($row['URL'])
                : Str::slug($row['Name'] ?? 'page');

            $faqs = null;

            if (!empty($row['FAQ Heading']) || !empty($row['FAQ Content'])) {
                $faqs = json_encode([
                    [
                        'question' => trim($row['FAQ Heading'] ?? ''),
                        'answer'   => trim($row['FAQ Content'] ?? ''),
                    ]
                ]);
            }

            Pages::updateOrCreate(
                ['url_slug' => $urlSlug],
                [
                    'location'         => $row['Location'] ?? null,
                    'category'         => $row['Category'] ?? null,
                    'name'             => $row['Name'] ?? null,
                    'image'            => $row['Banner Link'] ?? null,
                    'url_slug'         => $urlSlug,
                    'banner_content'   => $row['Banner Content'] ?? null,
                    'page_content'     => $row['Page Content'] ?? null,
                    'faqs'             => $faqs,
                    'meta_title'       => $row['Meta Title'] ?? null,
                    'meta_description' => $row['Meta Content'] ?? null,
                    'meta_keyword'     => $row['Meta Keyword'] ?? null,
                    'status'           => strtolower($row['Status'] ?? '') === 'active' ? 1 : 0,
                    'page_code'        => '#GD' . ($lastId + $index + 1),
                ]
            );
        }
    }
}
