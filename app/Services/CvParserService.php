<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;

class CvParserService
{
    /**
     * Parse candidate name, email, and mobile number from an uploaded CV file.
     */
    public function parse(UploadedFile $file): array
    {
        $rawText = $this->extractText($file);

        if (trim($rawText) === '') {
            return [
                'candidate_name' => null,
                'email' => null,
                'mobile_number' => null,
            ];
        }

        // Try AI extraction if API key configured
        $aiResult = $this->extractWithAi($rawText);
        if ($aiResult !== null && (! empty($aiResult['candidate_name']) || ! empty($aiResult['email']) || ! empty($aiResult['mobile_number']))) {
            return $aiResult;
        }

        // Fallback to local heuristic extraction
        return $this->extractHeuristically($rawText);
    }

    /**
     * Extract raw text from PDF, DOCX, or DOC.
     */
    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if ($extension === 'pdf') {
            return $this->extractFromPdf($path);
        }

        if ($extension === 'docx') {
            return $this->extractFromDocx($path);
        }

        if ($extension === 'doc') {
            return $this->extractFromDoc($path);
        }

        return '';
    }

    protected function extractFromPdf(string $path): string
    {
        if (file_exists('/usr/bin/pdftotext')) {
            $process = new Process(['/usr/bin/pdftotext', $path, '-']);
            $process->run();
            if ($process->isSuccessful()) {
                return $process->getOutput();
            }
        }

        return '';
    }

    protected function extractFromDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xml) {
                $xml = str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml);
                return strip_tags($xml);
            }
        }

        return '';
    }

    protected function extractFromDoc(string $path): string
    {
        // For old binary .doc, extract printable strings as best effort
        $content = @file_get_contents($path);
        if ($content === false) {
            return '';
        }

        $clean = preg_replace('/[^\x20-\x7E\r\n\t]/', ' ', $content);
        return preg_replace('/\s+/', ' ', $clean) ?? '';
    }

    /**
     * Heuristic parser using regex and text patterns.
     */
    public function extractHeuristically(string $text): array
    {
        $data = [
            'candidate_name' => null,
            'email' => null,
            'mobile_number' => null,
        ];

        // 1. Email extraction
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $emailMatch)) {
            $data['email'] = strtolower(trim($emailMatch[0]));
        }

        // 2. Mobile number extraction (Indian 10-digit / international)
        if (preg_match('/(?:(?:\+|00)91[\s-]?)?[6-9]\d{4}[\s-]?\d{5}/', $text, $phoneMatch)) {
            $cleaned = preg_replace('/[^\d+]/', '', $phoneMatch[0]);
            $data['mobile_number'] = $cleaned;
        } elseif (preg_match('/(?:\+?\d{1,3}[\s-]?)?\(?\d{3,4}\)?[\s-]?\d{3}[\s-]?\d{4}/', $text, $phoneMatch)) {
            $cleaned = preg_replace('/[^\d+]/', '', $phoneMatch[0]);
            $data['mobile_number'] = $cleaned;
        }

        // 3. Candidate Name extraction (examine the top lines of the resume)
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $ignoreKeywords = [
            'resume', 'curriculum', 'vitae', 'cv', 'profile', 'summary', 'biodata', 'bio-data',
            'contact', 'email', 'phone', 'address', 'page', 'objective', 'education', 'skills',
            'experience', 'developer', 'engineer', 'manager', 'consultant', 'analyst', 'designer',
            'linkedin', 'github', 'http', 'https', 'www', 'com', 'in'
        ];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }

            // Skip lines with email or phone numbers
            if (str_contains($trimmed, '@') || preg_match('/\d{5,}/', $trimmed)) {
                continue;
            }

            $words = preg_split('/\s+/', $trimmed);
            $wordCount = count($words);

            // Names typically consist of 2 to 4 words and 3 to 40 characters
            if ($wordCount >= 2 && $wordCount <= 4 && mb_strlen($trimmed) >= 3 && mb_strlen($trimmed) <= 40) {
                $isKeywordLine = false;
                foreach ($words as $word) {
                    if (in_array(strtolower($word), $ignoreKeywords, true)) {
                        $isKeywordLine = true;
                        break;
                    }
                }

                if (! $isKeywordLine && preg_match('/^[A-Za-z\s.\'-]+$/', $trimmed)) {
                    $data['candidate_name'] = ucwords(strtolower($trimmed));
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Optional AI extraction using short and strong prompt if API key configured.
     */
    protected function extractWithAi(string $text): ?array
    {
        $apiKey = env('GEMINI_API_KEY') ?: env('OPENAI_API_KEY');
        if (! $apiKey) {
            return null;
        }

        $snippet = mb_substr($text, 0, 4000); // first 4000 characters contain all key header info

        try {
            if (env('GEMINI_API_KEY')) {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "Extract ONLY these 3 fields from the candidate resume text:
- candidate_name: Full name of candidate
- email: Primary email address
- mobile_number: 10-15 digit phone number (include country code if present)

Resume Text:
\"\"\"
{$snippet}
\"\"\"

Return STRICT JSON ONLY without markdown or explanation:
{\"candidate_name\": \"string\", \"email\": \"string\", \"mobile_number\": \"string\"}
If any field cannot be found, set its value to null."
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $jsonText = $response->json('candidates.0.content.parts.0.text');
                    $cleanJson = trim(str_replace(['```json', '```'], '', $jsonText));
                    $decoded = json_decode($cleanJson, true);
                    if (is_array($decoded)) {
                        return [
                            'candidate_name' => $decoded['candidate_name'] ?? null,
                            'email' => $decoded['email'] ?? null,
                            'mobile_number' => $decoded['mobile_number'] ?? null,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CV AI extraction failed: '.$e->getMessage());
        }

        return null;
    }
}
