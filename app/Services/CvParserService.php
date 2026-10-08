<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;

class CvParserService
{
    /**
     * Parse candidate name, email, mobile number, job role, and remarks from an uploaded CV file.
     *
     * @param UploadedFile $file
     * @param array<int, string> $availableJobRoles [id => role_name]
     * @return array
     */
    public function parse(UploadedFile $file, array $availableJobRoles = []): array
    {
        $filename = $file->getClientOriginalName();
        $rawText = $this->extractText($file);

        // 1. Try AI-powered analysis if text is available
        if (trim($rawText) !== '') {
            $aiResult = $this->extractWithAi($rawText, $filename, $availableJobRoles);
            if ($aiResult !== null && (! empty($aiResult['candidate_name']) || ! empty($aiResult['email']) || ! empty($aiResult['mobile_number']))) {
                // Ensure common standardized formatting
                $aiResult = $this->standardizeCommonFormat($aiResult, $filename, $availableJobRoles);
                $aiResult['ai_powered'] = true;
                return $aiResult;
            }
        }

        // 2. Intelligent heuristic parser (combining text pattern parsing and filename conventions)
        $result = $this->extractHeuristically($rawText, $filename, $availableJobRoles);
        $result = $this->standardizeCommonFormat($result, $filename, $availableJobRoles);
        $result['ai_powered'] = false;

        return $result;
    }

    /**
     * Extract raw text from PDF, DOCX, DOC, RTF, ODT, or TXT.
     */
    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        $path = $file->getRealPath();

        if (! file_exists($path) || filesize($path) === 0) {
            return '';
        }

        // Inspect magic bytes to handle files with mismatched extensions
        $handle = @fopen($path, 'rb');
        $magic = $handle ? fread($handle, 8) : '';
        if ($handle) {
            fclose($handle);
        }

        $isZip = str_starts_with($magic, "PK\x03\x04");
        $isPdf = str_starts_with($magic, "%PDF");
        $isOleDoc = str_starts_with($magic, "\xD0\xCF\x11\xE0");
        $isRtf = str_starts_with($magic, "{\\rtf");

        if ($isZip || in_array($extension, ['docx', 'odt'], true)) {
            $docxText = $this->extractFromDocx($path);
            if (trim($docxText) !== '') {
                return $docxText;
            }
        }

        if ($isPdf || $extension === 'pdf') {
            $pdfText = $this->extractFromPdf($path);
            if (trim($pdfText) !== '') {
                return $pdfText;
            }
        }

        if ($isOleDoc || $extension === 'doc') {
            $docText = $this->extractFromDoc($path);
            if (trim($docText) !== '') {
                return $docText;
            }
        }

        if ($isRtf || $extension === 'rtf') {
            return $this->extractFromRtf($path);
        }

        if ($extension === 'txt') {
            return (string) @file_get_contents($path);
        }

        return '';
    }

    /**
     * Extract text from DOCX archive (including headers, footers, tables, and document body).
     */
    protected function extractFromDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $textParts = [];
        $xmlFiles = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if ($entryName && preg_match('#^word/(header\d*|document|footer\d*)\.xml$#i', $entryName)) {
                $xmlFiles[] = $entryName;
            }
        }

        // Sort so headers come first, then main document body, then footers
        usort($xmlFiles, function ($a, $b) {
            $priority = function ($file) {
                if (str_contains($file, 'header')) return 1;
                if (str_contains($file, 'document')) return 2;
                return 3;
            };
            return $priority($a) <=> $priority($b);
        });

        foreach ($xmlFiles as $file) {
            $xml = $zip->getFromName($file);
            if ($xml) {
                $xml = str_replace(
                    ['<w:br/>', '<w:br />', '<w:cr/>', '</w:p>', '</w:tr>', '</w:tc>', '<w:tab/>'],
                    ["\n", "\n", "\n", "\n", "\n", " \t ", " "],
                    $xml
                );
                $clean = strip_tags($xml);
                $clean = html_entity_decode($clean, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $trimmed = trim($clean);
                if ($trimmed !== '') {
                    $textParts[] = $trimmed;
                }
            }
        }

        $zip->close();

        return implode("\n", $textParts);
    }

    /**
     * Extract text from binary Microsoft Word 97-2003 (.doc) files.
     */
    protected function extractFromDoc(string $path): string
    {
        $content = @file_get_contents($path);
        if ($content === false || strlen($content) === 0) {
            return '';
        }

        $extractedLines = [];

        // 1. Extract UTF-16LE text sequences (sequences of 3+ Unicode chars: [\x20-\x7E\t\r\n]\x00)
        if (preg_match_all('/(?:[\x20-\x7E\t\r\n]\x00){3,}/', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $converted = @mb_convert_encoding($match, 'UTF-8', 'UTF-16LE');
                $trimmed = trim($converted);
                if (strlen($trimmed) > 2) {
                    $extractedLines[] = $trimmed;
                }
            }
        }

        // 2. Extract 8-bit ASCII sequences
        if (preg_match_all('/[\x20-\x7E\t\r\n]{4,}/', $content, $matches)) {
            foreach ($matches[0] as $match) {
                $trimmed = trim($match);
                if (strlen($trimmed) > 3 && ! preg_match('/^(?:WordDocument|SummaryInformation|DocumentSummaryInformation|CompObj|ObjectPool)$/i', $trimmed)) {
                    $extractedLines[] = $trimmed;
                }
            }
        }

        return implode("\n", $extractedLines);
    }

    /**
     * Extract text from PDF using pdftotext CLI and pure PHP fallback.
     */
    protected function extractFromPdf(string $path): string
    {
        if (file_exists('/usr/bin/pdftotext')) {
            $process = new Process(['/usr/bin/pdftotext', '-layout', $path, '-']);
            $process->run();
            if ($process->isSuccessful() && trim($process->getOutput()) !== '') {
                return $process->getOutput();
            }

            $processSimple = new Process(['/usr/bin/pdftotext', $path, '-']);
            $processSimple->run();
            if ($processSimple->isSuccessful() && trim($processSimple->getOutput()) !== '') {
                return $processSimple->getOutput();
            }
        }

        return $this->extractPdfTextPurePhp($path);
    }

    /**
     * Pure PHP PDF stream extractor fallback.
     */
    protected function extractPdfTextPurePhp(string $path): string
    {
        $content = @file_get_contents($path);
        if (! $content) {
            return '';
        }

        $text = '';
        if (preg_match_all('#stream[\r\n]+(.*?)[\r\n]+endstream#s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $decompressed = @gzuncompress($stream);
                if (! $decompressed) {
                    $decompressed = @gzinflate($stream);
                }
                $streamData = $decompressed ?: $stream;

                if (preg_match_all('#\((.*?)\)\s*Tj#s', $streamData, $tMatches)) {
                    $text .= implode(' ', $tMatches[1]) . "\n";
                }
                if (preg_match_all('#\[(.*?)\]\s*TJ#s', $streamData, $tjMatches)) {
                    foreach ($tjMatches[1] as $tj) {
                        if (preg_match_all('#\((.*?)\)#s', $tj, $parts)) {
                            $text .= implode('', $parts[1]) . ' ';
                        }
                    }
                    $text .= "\n";
                }
            }
        }

        $text = preg_replace('/\\\\[0-7]{1,3}/', '', $text);
        $text = str_replace(['\\(', '\\)', '\\\\', '\\n', '\\r', '\\t'], ['(', ')', '\\', "\n", "\r", "\t"], $text);

        return $text;
    }

    /**
     * Extract text from RTF.
     */
    protected function extractFromRtf(string $path): string
    {
        $content = @file_get_contents($path);
        if (! $content) {
            return '';
        }
        $text = preg_replace('/[{}]/', '', $content);
        $text = preg_replace('/\\\\[a-z]+[0-9-]* ?/i', ' ', $text);
        $text = preg_replace('/\\\\\'.{2}/', ' ', $text);
        return preg_replace('/\s+/', ' ', $text) ?? '';
    }

    /**
     * AI-powered CV extraction using Google Gemini or OpenAI.
     */
    public function extractWithAi(string $text, ?string $filename = null, array $availableJobRoles = []): ?array
    {
        $geminiKey = null;
        $openAiKey = null;

        try {
            $geminiKey = config('services.gemini.key');
            $openAiKey = config('services.openai.key');
        } catch (\Throwable $e) {
            // Container not bound in isolated unit tests
        }

        $geminiKey = $geminiKey ?: env('GEMINI_API_KEY') ?: $this->getSetting('gemini_api_key');
        $openAiKey = $openAiKey ?: env('OPENAI_API_KEY') ?: $this->getSetting('openai_api_key');

        if (! $geminiKey && ! $openAiKey) {
            return null;
        }

        $snippet = mb_substr($text, 0, 5000);
        $rolesList = !empty($availableJobRoles) ? json_encode($availableJobRoles, JSON_UNESCAPED_UNICODE) : '[]';

        $prompt = "You are an expert HR recruiting assistant. Analyze the candidate resume and extract the candidate's details into valid, clean JSON.

Available Job Roles in the company (id => title):
{$rolesList}

Resume Text:
\"\"\"
{$snippet}
\"\"\"

Filename: \"{$filename}\"

Instructions:
1. candidate_name: Full name of candidate in Title Case (e.g. \"Manoj Kumar MK\", \"Murugan\", \"T Udayakumar\", \"Anmol Dubey\").
2. email: Primary personal email address. Do not return job board emails (e.g. naukri.com, monster.com, indeed.com).
3. mobile_number: Primary contact phone number (10-digit number or international with country code).
4. need: Formatted summary of candidate's experience, current designation, and notice period if mentioned (e.g. \"Exp: 16 Years | Senior Software Engineer | Notice: 15 Days\").
5. job_role_id: Integer ID from the Available Job Roles list that best matches the candidate's title/skills, or null if no strong match.
6. job_role_name: Title of the matched job role, or null.

Return STRICT JSON ONLY matching this format without markdown code blocks:
{\"candidate_name\": \"string or null\", \"email\": \"string or null\", \"mobile_number\": \"string or null\", \"need\": \"string or null\", \"job_role_id\": null, \"job_role_name\": null}";

        // 1. Try Gemini API
        if ($geminiKey) {
            try {
                $model = 'gemini-1.5-flash';
                try {
                    $model = config('services.gemini.model') ?: $model;
                } catch (\Throwable $e) {}

                $response = Http::timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature' => 0.1,
                    ],
                ]);

                if ($response->successful()) {
                    $jsonText = $response->json('candidates.0.content.parts.0.text');
                    $cleanJson = trim(str_replace(['```json', '```'], '', (string) $jsonText));
                    $decoded = json_decode($cleanJson, true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                } else {
                    Log::warning('Gemini CV parse response status: ' . $response->status() . ' body: ' . substr($response->body(), 0, 300));
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini CV parse exception: ' . $e->getMessage());
            }
        }

        // 2. Try OpenAI API
        if ($openAiKey) {
            try {
                $model = 'gpt-4o-mini';
                try {
                    $model = config('services.openai.model') ?: $model;
                } catch (\Throwable $e) {}

                $response = Http::withToken($openAiKey)->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert HR recruiting assistant. Output valid JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.1,
                ]);

                if ($response->successful()) {
                    $jsonText = $response->json('choices.0.message.content');
                    $decoded = json_decode((string) $jsonText, true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                } else {
                    Log::warning('OpenAI CV parse response status: ' . $response->status());
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI CV parse exception: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Heuristic parser using regex, text patterns, and filename metadata.
     */
    public function extractHeuristically(string $text, ?string $filename = null, array $availableJobRoles = []): array
    {
        $data = [
            'candidate_name' => null,
            'email' => null,
            'mobile_number' => null,
            'need' => null,
            'job_role_id' => null,
            'job_role_name' => null,
        ];

        // 1. Email extraction (filter out portal and support emails)
        $data['email'] = $this->extractEmail($text);

        // 2. Mobile number extraction
        $data['mobile_number'] = $this->extractMobile($text);

        // 3. Experience / Remarks extraction
        $data['need'] = $this->extractRemarks($text, $filename);

        // 4. Candidate Name extraction (Text first, then filename fallback)
        $data['candidate_name'] = $this->extractCandidateName($text, $filename);

        // 5. Match Job Role from available job roles
        $matchedRole = $this->matchJobRole($text, $availableJobRoles);
        if ($matchedRole) {
            $data['job_role_id'] = $matchedRole['id'];
            $data['job_role_name'] = $matchedRole['name'];
        }

        return $data;
    }

    /**
     * Standardize values into common formats for auto-appending into form inputs.
     */
    protected function standardizeCommonFormat(array $data, ?string $filename = null, array $availableJobRoles = []): array
    {
        // 1. Candidate Name
        if (! empty($data['candidate_name'])) {
            $name = trim(preg_replace('/\s+/', ' ', $data['candidate_name']));
            $data['candidate_name'] = ucwords(strtolower($name));
        } elseif ($filename) {
            $fnData = $this->extractFromFilename($filename);
            if (! empty($fnData['candidate_name'])) {
                $data['candidate_name'] = $fnData['candidate_name'];
            }
        }

        // 2. Email (clean lowercase)
        if (! empty($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // 3. Mobile Number (clean 10-digit or valid international)
        if (! empty($data['mobile_number'])) {
            $raw = preg_replace('/[^\d+]/', '', $data['mobile_number']);
            $digits = preg_replace('/[^\d]/', '', $raw);
            if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
                $data['mobile_number'] = substr($digits, 2);
            } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $data['mobile_number'] = substr($digits, 1);
            } else {
                $data['mobile_number'] = $raw;
            }
        }

        // 4. Remarks (need)
        if (empty($data['need']) && $filename) {
            $fnData = $this->extractFromFilename($filename);
            if (! empty($fnData['need'])) {
                $data['need'] = $fnData['need'];
            }
        }

        // 5. Job Role ID
        if (empty($data['job_role_id']) && ! empty($availableJobRoles)) {
            $matched = $this->matchJobRole($data['need'] ?? '', $availableJobRoles);
            if ($matched) {
                $data['job_role_id'] = $matched['id'];
                $data['job_role_name'] = $matched['name'];
            }
        }

        return $data;
    }

    /**
     * Extract primary candidate email address.
     */
    protected function extractEmail(string $text): ?string
    {
        $ignoreDomains = [
            'naukri.com', 'monster.com', 'indeed.com', 'shine.com', 'linkedin.com',
            'timesjobs.com', 'jobseeker.com', 'example.com', 'test.com', 'gmail.con',
        ];

        // Explicitly labelled email
        if (preg_match('/(?:Email|E-mail|Mail)(?:\s*(?:ID|Address))?\s*[:\-]?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $text, $m)) {
            $email = strtolower(trim($m[1]));
            $domain = substr(strrchr($email, '@'), 1);
            if (! in_array($domain, $ignoreDomains, true)) {
                return $email;
            }
        }

        // Scan all emails in text
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            foreach ($matches[0] as $candidateEmail) {
                $candidateEmail = strtolower(trim($candidateEmail));
                $domain = substr(strrchr($candidateEmail, '@'), 1);
                if (! in_array($domain, $ignoreDomains, true) && ! preg_match('/^(noreply|no-reply|feedback|support|help|info|contact|sales|billing)@/i', $candidateEmail)) {
                    return $candidateEmail;
                }
            }
        }

        return null;
    }

    /**
     * Extract primary candidate mobile number.
     */
    protected function extractMobile(string $text): ?string
    {
        $cleanNumber = null;

        // Check explicitly labelled mobile
        if (preg_match('/(?:Mobile|Phone|Contact|Cell|Tel|Ph|Mob)(?:\s*(?:No|Number|ID))?\s*[:\-]?\s*([+\d\s\-()]{10,22})/i', $text, $m)) {
            $raw = preg_replace('/[^\d+]/', '', $m[1]);
            $digitsOnly = preg_replace('/[^\d]/', '', $raw);
            if (strlen($digitsOnly) >= 10 && strlen($digitsOnly) <= 15) {
                $cleanNumber = $raw;
            }
        }

        // Pattern-based fallback (Indian mobile or international)
        if (! $cleanNumber) {
            if (preg_match('/(?:(?:\+|00)91[\s-]?)?[6-9]\d{2,4}[\s-]?\d{2,4}[\s-]?\d{2,4}/', $text, $pm)) {
                $cleanNumber = preg_replace('/[^\d+]/', '', $pm[0]);
            } elseif (preg_match('/(?:\+?\d{1,3}[\s-]?)?\(?\d{3,4}\)?[\s-]?\d{3}[\s-]?\d{4}/', $text, $pm)) {
                $cleanNumber = preg_replace('/[^\d+]/', '', $pm[0]);
            }
        }

        if (! $cleanNumber) {
            return null;
        }

        $digits = preg_replace('/[^\d]/', '', $cleanNumber);
        if (preg_match('/^(\d)\1{9,}$/', $digits)) {
            return null;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $cleanNumber;
    }

    /**
     * Extract experience, notice period, or designation for remarks.
     */
    protected function extractRemarks(string $text, ?string $filename = null): ?string
    {
        $expFound = null;

        // 1. Check text for Total Experience
        if (preg_match('/(?:Total\s+Experience|Experience)\s*[:\-]?\s*([0-9.]+\s*(?:Year\(s\)|Years?|Yrs?)(?:\s*[0-9.]+\s*(?:Month\(s\)|Months?|Mths?))?)/i', $text, $em)) {
            $expFound = trim($em[1]);
        }

        // 2. Check filename for Naukri bracket format: [16y_0m]
        if (! $expFound && $filename && preg_match('/\[(\d+)y(?:_(\d+)m)?\]/i', $filename, $fm)) {
            $years = (int) $fm[1];
            $months = isset($fm[2]) ? (int) $fm[2] : 0;
            $expFound = $months > 0 ? "{$years}y {$months}m" : "{$years} Years";
        }

        // Check Notice Period
        $notice = null;
        if (preg_match('/Notice\s+Period\s*[:\-]?\s*([A-Za-z0-9\s]+?)(?:\n|\r|\||,|$)/i', $text, $nm)) {
            $notice = trim($nm[1]);
            if (strlen($notice) > 30) $notice = null;
        }

        // Check Current Designation
        $designation = null;
        if (preg_match('#Current\s+Designation\s*[:\-]?\s*([A-Za-z0-9\s/&.,-]+?)(?:\n|\r|\||$)#i', $text, $dm)) {
            $designation = trim($dm[1]);
            if (strlen($designation) > 40) $designation = null;
        }

        $parts = [];
        if ($expFound) $parts[] = "Exp: {$expFound}";
        if ($designation) $parts[] = $designation;
        if ($notice) $parts[] = "Notice: {$notice}";

        return !empty($parts) ? implode(' | ', $parts) : null;
    }

    /**
     * Extract candidate name from text or filename.
     */
    protected function extractCandidateName(string $text, ?string $filename = null): ?string
    {
        // Explicit name label in text
        if (preg_match('/(?:Candidate\s+Name|Full\s+Name|Name\s*of\s*Candidate|Name)\s*[:\-]\s*([A-Za-z\t .\'-]{2,40})/i', $text, $nm)) {
            $candidateName = trim($nm[1]);
            $firstWord = strtolower(strtok($candidateName, ' '));
            if (! in_array($firstWord, ['of', 'in', 'for', 'at', 'the', 'profile', 'details', 'summary', 'resume'], true)) {
                return ucwords(strtolower($candidateName));
            }
        }

        // Top lines scan
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $ignoreKeywords = [
            'resume', 'curriculum', 'vitae', 'cv', 'profile', 'summary', 'biodata', 'bio-data',
            'contact', 'email', 'phone', 'address', 'page', 'objective', 'education', 'skills',
            'experience', 'developer', 'engineer', 'manager', 'consultant', 'analyst', 'designer',
            'linkedin', 'github', 'http', 'https', 'www', 'com', 'in', 'naukri', 'technical', 'professional',
        ];

        foreach (array_slice($lines, 0, 20) as $line) {
            $trimmed = trim(preg_replace('/\s+/', ' ', $line));
            if (empty($trimmed) || str_contains($trimmed, '@') || preg_match('/\d{5,}/', $trimmed)) {
                continue;
            }

            if (str_contains($trimmed, ':') || str_contains($trimmed, '|')) {
                continue;
            }

            $cleanLine = trim(preg_replace('/[^A-Za-z\s.\'-]/', '', $trimmed));
            $words = preg_split('/\s+/', $cleanLine);
            $wordCount = count($words);

            if ($wordCount >= 1 && $wordCount <= 5 && mb_strlen($cleanLine) >= 3 && mb_strlen($cleanLine) <= 40) {
                $isKeywordLine = false;
                foreach ($words as $w) {
                    if (in_array(strtolower($w), $ignoreKeywords, true)) {
                        $isKeywordLine = true;
                        break;
                    }
                }
                if (! $isKeywordLine) {
                    return ucwords(strtolower($cleanLine));
                }
            }
        }

        // Filename fallback
        if ($filename) {
            $fromFilename = $this->extractFromFilename($filename);
            if (! empty($fromFilename['candidate_name'])) {
                return $fromFilename['candidate_name'];
            }
        }

        return null;
    }

    /**
     * Extract candidate name and experience directly from filename conventions.
     */
    public function extractFromFilename(string $filename): array
    {
        $result = [
            'candidate_name' => null,
            'need' => null,
        ];

        // 1. Naukri format: Naukri_<Name>[<exp>].ext or Naukri_<Name>.ext
        if (preg_match('/^Naukri[_\-\s]+([A-Za-z0-9_\s.\'-]+?)(?:\[(\d+y(?:_\d+m)?)\]|\.[a-zA-Z0-9]+$|$)/i', $filename, $m)) {
            $rawName = trim($m[1]);
            $formatted = preg_replace('/([a-z])([A-Z])/', '$1 $2', $rawName);
            $formatted = preg_replace('/^([A-Z])([A-Z][a-z])/', '$1 $2', $formatted);
            $formatted = preg_replace('/([a-z])([A-Z]{1,3})$/', '$1 $2', $formatted);
            $formatted = str_replace('_', ' ', $formatted);
            $result['candidate_name'] = ucwords(strtolower(trim($formatted)));

            if (! empty($m[2])) {
                $exp = str_replace('_', ' ', $m[2]);
                $result['need'] = "Exp: {$exp}";
            }
            return $result;
        }

        // 2. Standard format: Resume_<Name> or CV_<Name>
        if (preg_match('/^(?:Resume|CV)[_\-\s]+([A-Za-z0-9_\s.\'-]+?)(?:\.[a-zA-Z0-9]+$|$)/i', $filename, $m) ||
            preg_match('/^([A-Za-z0-9_\s.\'-]+?)[_\-\s]+(?:Resume|CV)(?:\.[a-zA-Z0-9]+$|$)/i', $filename, $m)) {
            $rawName = str_replace('_', ' ', trim($m[1]));
            $result['candidate_name'] = ucwords(strtolower($rawName));
        }

        return $result;
    }

    /**
     * Match text against available company Job Roles.
     */
    protected function matchJobRole(string $text, array $availableJobRoles): ?array
    {
        if (empty($availableJobRoles)) {
            return null;
        }

        $lowerText = strtolower($text);

        foreach ($availableJobRoles as $id => $roleName) {
            $lowerRole = strtolower(trim((string) $roleName));
            if ($lowerRole === '') continue;

            // Direct substring match
            if (str_contains($lowerText, $lowerRole)) {
                return ['id' => (int) $id, 'name' => $roleName];
            }

            // Word token match for multi-word roles (e.g. "Full Stack Developer")
            $roleWords = array_filter(explode(' ', $lowerRole), fn($w) => strlen($w) > 2);
            if (count($roleWords) >= 2) {
                $allMatched = true;
                foreach ($roleWords as $rw) {
                    if (! str_contains($lowerText, $rw)) {
                        $allMatched = false;
                        break;
                    }
                }
                if ($allMatched) {
                    return ['id' => (int) $id, 'name' => $roleName];
                }
            }
        }

        return null;
    }

    /**
     * Helper to safely read a setting from database if table exists.
     */
    protected function getSetting(string $key): ?string
    {
        try {
            return Setting::where('key', $key)->value('value');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
