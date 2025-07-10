<?php

namespace App\Services;

use App\Models\JobSeeker\Resume;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TextExtractionService
{
    /**
     * Extract text from resume file
     */
    public function extractTextFromResume(Resume $resume): string
    {
        $filePath = Storage::disk('public')->path($resume->file_path);

        if (! file_exists($filePath)) {
            throw new \Exception("Resume file not found: {$filePath}");
        }

        $extractedText = match ($resume->file_type) {
            'pdf' => $this->extractFromPdf($filePath),
            'doc' => $this->extractFromDoc($filePath),
            'docx' => $this->extractFromDocx($filePath),
            default => throw new \Exception("Unsupported file type: {$resume->file_type}")
        };

        // Clean and normalize extracted text
        $cleanText = $this->cleanExtractedText($extractedText);

        // Update resume with extracted text
        $resume->update([
            'extracted_text' => $cleanText,
            'text_extracted' => true,
        ]);

        Log::info('Text extracted from resume', [
            'resume_id' => $resume->id,
            'text_length' => strlen($cleanText),
            'file_type' => $resume->file_type,
        ]);

        return $cleanText;
    }

    /**
     * Extract text from PDF file
     */
    private function extractFromPdf(string $filePath): string
    {
        try {
            // Use smalot/pdfparser for robust PDF text extraction
            if (! class_exists('Smalot\\PdfParser\\Parser')) {
                throw new \Exception('smalot/pdfparser is not installed. Run: composer require smalot/pdfparser');
            }

            $parser = new \Smalot\PdfParser\Parser;
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            return $text ?: 'Unable to extract text from PDF';

        } catch (\Exception $e) {
            Log::error('PDF text extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to extract text from PDF: '.$e->getMessage());
        }
    }

    /**
     * Extract readable text from PDF content
     */
    private function extractReadableTextFromPdf(string $content): string
    {
        $text = '';

        // Remove null bytes and control characters
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $content);

        // Extract text between common PDF delimiters
        $patterns = [
            '/BT\s*(.*?)\s*ET/s',  // Text objects
            '/Td\s*\[(.*?)\]/s',   // Text arrays
            '/Tj\s*\[(.*?)\]/s',   // Show text
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $match) {
                    $decoded = $this->decodePdfText($match);
                    if (! empty($decoded)) {
                        $text .= $decoded.' ';
                    }
                }
            }
        }

        return trim($text);
    }

    /**
     * Decode PDF text content
     */
    private function decodePdfText(string $text): string
    {
        // Remove PDF escape sequences
        $text = preg_replace('/\\\\[0-9]{3}/', ' ', $text);
        $text = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)'], [' ', ' ', ' ', '(', ')'], $text);

        // Extract readable characters
        $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $text);

        return trim($text);
    }

    /**
     * Extract text from DOC file
     */
    private function extractFromDoc(string $filePath): string
    {
        try {
            // Basic DOC file text extraction
            // For production use, consider PhpOffice/PhpWord for better DOC support

            $content = file_get_contents($filePath);
            $text = '';

            if (($fh = fopen($filePath, 'r')) !== false) {
                // Read DOC file header to get text length
                $headers = fread($fh, 0xA00);

                if (strlen($headers) >= 0x21F) {
                    $n1 = (ord($headers[0x21C]) - 1);
                    $n2 = ((ord($headers[0x21D]) - 8) * 256);
                    $n3 = ((ord($headers[0x21E]) * 256) * 256);
                    $n4 = (((ord($headers[0x21F]) * 256) * 256) * 256);
                    $textLength = ($n1 + $n2 + $n3 + $n4);

                    if ($textLength > 0 && $textLength < 1000000) { // Sanity check
                        $extracted = fread($fh, $textLength);
                        $text = mb_convert_encoding($extracted, 'UTF-8', 'UTF-16LE');
                    }
                }
                fclose($fh);
            }

            // Fallback: extract printable characters
            if (empty($text)) {
                $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $content);
            }

            return $text ?: 'Unable to extract text from DOC file';

        } catch (\Exception $e) {
            Log::error('DOC text extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to extract text from DOC: '.$e->getMessage());
        }
    }

    /**
     * Extract text from DOCX file
     */
    private function extractFromDocx(string $filePath): string
    {
        try {
            $zip = new \ZipArchive;
            $text = '';

            if ($zip->open($filePath) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();

                if ($xml !== false) {
                    $xmlData = simplexml_load_string($xml);

                    if ($xmlData !== false) {
                        $xmlData->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                        $textNodes = $xmlData->xpath('//w:t');
                        foreach ($textNodes as $textNode) {
                            $text .= (string) $textNode.' ';
                        }
                    }
                }
            }

            return trim($text) ?: 'Unable to extract text from DOCX file';

        } catch (\Exception $e) {
            Log::error('DOCX text extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to extract text from DOCX: '.$e->getMessage());
        }
    }

    /**
     * Clean and normalize extracted text
     */
    private function cleanExtractedText(string $text): string
    {
        // Remove null bytes and control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove excessive punctuation
        $text = preg_replace('/[\.]{2,}/', '.', $text);
        $text = preg_replace('/[,]{2,}/', ',', $text);

        // Trim and ensure proper encoding
        $text = trim($text);
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        return $text;
    }

    /**
     * Get text statistics for analysis
     */
    public function getTextStatistics(string $text): array
    {
        $words = str_word_count($text, 1);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return [
            'character_count' => strlen($text),
            'word_count' => count($words),
            'sentence_count' => count($sentences),
            'average_word_length' => count($words) > 0 ? strlen(implode('', $words)) / count($words) : 0,
            'readability_score' => $this->calculateReadabilityScore($words, $sentences),
        ];
    }

    /**
     * Simple readability score calculation
     */
    private function calculateReadabilityScore(array $words, array $sentences): float
    {
        if (count($sentences) == 0 || count($words) == 0) {
            return 0;
        }

        $avgWordsPerSentence = count($words) / count($sentences);
        $avgSyllablesPerWord = $this->countSyllables($words) / count($words);

        // Simplified Flesch Reading Ease formula
        return 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);
    }

    /**
     * Count syllables in words (approximation)
     */
    private function countSyllables(array $words): int
    {
        $totalSyllables = 0;

        foreach ($words as $word) {
            $word = strtolower($word);
            $syllables = preg_match_all('/[aeiouy]+/', $word);

            // Adjust for silent 'e'
            if (substr($word, -1) === 'e') {
                $syllables--;
            }

            // Minimum 1 syllable per word
            $totalSyllables += max(1, $syllables);
        }

        return $totalSyllables;
    }

    /**
     * Validate file type and size
     */
    public function validateResumeFile(string $filePath, string $fileType): bool
    {
        // Check if file exists
        if (! file_exists($filePath)) {
            throw new \Exception("File does not exist: {$filePath}");
        }

        // Check file size (max 2MB)
        $fileSize = filesize($filePath);
        if ($fileSize > 2 * 1024 * 1024) {
            throw new \Exception('File size exceeds 2MB limit');
        }

        // Check supported file types
        $supportedTypes = ['pdf', 'doc', 'docx'];
        if (! in_array(strtolower($fileType), $supportedTypes)) {
            throw new \Exception("Unsupported file type: {$fileType}");
        }

        return true;
    }
}
