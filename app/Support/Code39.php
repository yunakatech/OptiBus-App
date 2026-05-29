<?php

namespace App\Support;

class Code39
{
    /**
     * @var array<string, string>
     */
    private const PATTERNS = [
        '0' => 'nnnwwnwnn',
        '1' => 'wnnwnnnnw',
        '2' => 'nnwwnnnnw',
        '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw',
        '5' => 'wnnwwnnnn',
        '6' => 'nnwwwnnnn',
        '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn',
        '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw',
        'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn',
        'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn',
        'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw',
        'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn',
        'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww',
        'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn',
        'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn',
        'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww',
        'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn',
        'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw',
        'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn',
        'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn',
        'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw',
        '.' => 'wwnnnnwnn',
        ' ' => 'nwwnnnwnn',
        '$' => 'nwnwnwnnn',
        '/' => 'nwnwnnnwn',
        '+' => 'nwnnnwnwn',
        '%' => 'nnnwnwnwn',
        '*' => 'nwnnwnwnn',
    ];

    public static function svgDataUri(string $value, int $height = 68): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svg($value, $height));
    }

    public static function svg(string $value, int $height = 68): string
    {
        $clean = strtoupper(trim($value));
        $filtered = '';

        foreach (str_split($clean) as $char) {
            if (isset(self::PATTERNS[$char]) && $char !== '*') {
                $filtered .= $char;
            }
        }

        $encoded = '*'.$filtered.'*';
        $narrow = 2;
        $wide = 5;
        $gap = 2;
        $textSpace = 18;
        $x = 8;
        $bars = [];

        foreach (str_split($encoded) as $char) {
            $pattern = self::PATTERNS[$char] ?? self::PATTERNS['*'];

            foreach (str_split($pattern) as $index => $unit) {
                $width = $unit === 'w' ? $wide : $narrow;

                if ($index % 2 === 0) {
                    $bars[] = sprintf(
                        '<rect x="%d" y="8" width="%d" height="%d" rx="0.4" ry="0.4" fill="#0f172a" />',
                        $x,
                        $width,
                        $height
                    );
                }

                $x += $width;
            }

            $x += $gap;
        }

        $svgWidth = max($x + 8, 220);
        $displayText = htmlspecialchars($filtered, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d" role="img" aria-label="Barcode %3$s"><rect width="100%%" height="100%%" fill="#ffffff"/>%4$s<text x="%5$d" y="%6$d" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" letter-spacing="2" fill="#334155">%3$s</text></svg>',
            $svgWidth,
            $height + $textSpace + 16,
            $displayText,
            implode('', $bars),
            (int) floor($svgWidth / 2),
            $height + $textSpace + 4,
        );
    }
}
