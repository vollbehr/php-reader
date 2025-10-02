<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

use Vollbehr\Media\Id3v1;

/**
 * The _Content type_ frame represents the genre of the audio.
 *
 * ID3v2.x stores the value as a string, but both ID3v2.3 and ID3v2.4 allow
 * referencing the legacy ID3v1 numeric genre list. This implementation keeps
 * the raw frame payload intact while exposing helpers that resolve numeric
 * references and handle refinements according to the specification.
 *
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Tcon extends \Vollbehr\Media\Id3\TextFrame
{
    private const SPECIAL_GENRES = [
        'RX' => 'Remix',
        'CR' => 'Cover',
    ];

    /**
     * Resolved genre entries in declaration order.
     *
     * @var array<int, array{type: 'id3v1'|'literal'|'special', value: int|string, text: string}>
     */
    private array $_genres = [];

    /**
     * Constructs the frame and parses any existing payload.
     *
     * @param \Vollbehr\Io\Reader|null $reader
     * @param array<string, mixed> $options
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->parseGenres();
    }

    /**
     * {@inheritDoc}
     */
    public function setText($text, $encoding = null): void
    {
        parent::setText($text, $encoding);
        $this->parseGenres();
    }

    /**
     * Returns the resolved genre strings (including refinements).
     *
     * @return array<int, string>
     */
    public function getGenres(): array
    {
        return array_map(
            static fn(array $entry): string => $entry['text'],
            $this->_genres
        );
    }

    /**
     * Returns the numeric ID3v1 genre codes that were resolved from the frame.
     *
     * @return array<int, int>
     */
    public function getGenreCodes(): array
    {
        $codes = [];
        foreach ($this->_genres as $entry) {
            if ($entry['type'] === 'id3v1') {
                $codes[] = (int) $entry['value'];
            }
        }

        return $codes;
    }

    /**
     * Replaces the genre information using strings, ID3v1 numeric codes, or
     * frame-formatted references (e.g. "(4)Eurodisco").
     *
     * @param array<int, int|string> $genres
     */
    public function setGenres(array $genres): void
    {
        $this->_genres = [];
        foreach ($genres as $genre) {
            foreach ($this->normaliseGenre($genre) as $entry) {
                $this->_genres[] = $entry;
            }
        }
        $this->_genres = array_values($this->_genres);

        $this->synchroniseTextFromGenres();
    }

    /**
     * Appends a genre entry to the frame data.
     *
     * @param int|string $genre
     */
    public function addGenre(int|string $genre): void
    {
        foreach ($this->normaliseGenre($genre) as $entry) {
            $this->_genres[] = $entry;
        }
        $this->_genres = array_values($this->_genres);

        $this->synchroniseTextFromGenres();
    }

    /**
     * Parses the current text payload and refreshes the resolved genres cache.
     */
    private function parseGenres(): void
    {
        $entries = [];
        foreach ($this->_text as $chunk) {
            $entries = array_merge($entries, $this->parseChunk((string) $chunk));
        }

        $this->_genres = array_values($entries);
    }

    /**
     * Parses a single text chunk from the frame into normalised entries.
     *
     * @return array<int, array{type: 'id3v1'|'literal'|'special', value: int|string, text: string}>
     */
    private function parseChunk(string $chunk): array
    {
        $chunk = trim($chunk);
        if ($chunk === '') {
            return [];
        }

        if ($this->isNumericRepresentation($chunk)) {
            return [$this->buildId3v1Entry((int) $chunk)];
        }

        $upperChunk = strtoupper($chunk);
        if (isset(self::SPECIAL_GENRES[$upperChunk])) {
            return [$this->buildSpecialEntry($upperChunk)];
        }

        $specialByName = $this->findSpecialCodeByName($chunk);
        if ($specialByName !== null) {
            return [$this->buildSpecialEntry($specialByName)];
        }

        $length = strlen($chunk);
        $buffer = '';
        $entries = [];

        for ($i = 0; $i < $length; $i++) {
            $char = $chunk[$i];
            if ($char === '(') {
                if ($i + 1 < $length && $chunk[$i + 1] === '(') {
                    $buffer .= '(';
                    $i++;
                    continue;
                }

                if ($buffer !== '') {
                    $literal = $this->buildLiteralEntry($buffer);
                    if ($literal !== null) {
                        $entries[] = $literal;
                    }
                    $buffer = '';
                }

                $closing = strpos($chunk, ')', $i + 1);
                if ($closing === false) {
                    $buffer .= substr($chunk, $i);
                    break;
                }

                $token = substr($chunk, $i + 1, $closing - $i - 1);
                $i      = $closing;
                $entry  = $this->resolveToken($token);
                if ($entry !== null) {
                    $entries[] = $entry;
                } else {
                    $literal = $this->buildLiteralEntry('(' . $token . ')');
                    if ($literal !== null) {
                        $entries[] = $literal;
                    }
                }

                continue;
            }

            $buffer .= $char;
        }

        if ($buffer !== '') {
            $literal = $this->buildLiteralEntry($buffer);
            if ($literal !== null) {
                $entries[] = $literal;
            }
        }

        return $entries;
    }

    /**
     * Normalises a user-supplied genre into frame entries.
     *
     * @param int|string $genre
     * @return array<int, array{type: 'id3v1'|'literal'|'special', value: int|string, text: string}>
     */
    private function normaliseGenre(int|string $genre): array
    {
        if (is_int($genre)) {
            return [$this->buildId3v1Entry($genre)];
        }

        $genre = trim((string) $genre);
        if ($genre === '') {
            return [];
        }

        if ($this->isNumericRepresentation($genre)) {
            return [$this->buildId3v1Entry((int) $genre)];
        }

        $upper = strtoupper($genre);
        if (isset(self::SPECIAL_GENRES[$upper])) {
            return [$this->buildSpecialEntry($upper)];
        }

        $specialByName = $this->findSpecialCodeByName($genre);
        if ($specialByName !== null) {
            return [$this->buildSpecialEntry($specialByName)];
        }

        $code = array_search($genre, Id3v1::$genres, true);
        if ($code === false) {
            $code = $this->findId3v1CodeByName($genre);
        }

        if ($code !== false && $code !== null) {
            return [$this->buildId3v1Entry((int) $code)];
        }

        return $this->parseChunk($genre);
    }

    /**
     * Resolves a token from within a parenthesised reference.
     */
    private function resolveToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        if ($this->isNumericRepresentation($token)) {
            return $this->buildId3v1Entry((int) $token);
        }

        $upperToken = strtoupper($token);
        if (isset(self::SPECIAL_GENRES[$upperToken])) {
            return $this->buildSpecialEntry($upperToken);
        }

        return null;
    }

    private function isNumericRepresentation(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        if (!preg_match('/^[0-9]+$/', $value)) {
            return false;
        }

        $number = (int) $value;

        return $number >= 0 && $number <= 255;
    }

    private function buildId3v1Entry(int $code): array
    {
        $name = Id3v1::$genres[$code] ?? null;
        if ($name === null && $code === 255) {
            $name = 'Unknown';
        }
        if ($name === null) {
            $name = (string) $code;
        }

        return [
            'type' => 'id3v1',
            'value' => $code,
            'text' => $name,
        ];
    }

    private function buildSpecialEntry(string $code): array
    {
        return [
            'type' => 'special',
            'value' => $code,
            'text' => self::SPECIAL_GENRES[$code],
        ];
    }

    private function buildLiteralEntry(string $text): ?array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return null;
        }

        return [
            'type' => 'literal',
            'value' => $trimmed,
            'text' => $trimmed,
        ];
    }

    private function synchroniseTextFromGenres(): void
    {
        $version = (float) $this->getOption('version', 4.0);

        if ($version >= 4.0) {
            $texts = [];
            foreach ($this->_genres as $entry) {
                $encoded = $this->encodeEntry($entry, true);
                if ($encoded !== '') {
                    $texts[] = $encoded;
                }
            }
            $this->_text = $texts;

            return;
        }

        $buffer = '';
        foreach ($this->_genres as $entry) {
            $buffer .= $this->encodeEntry($entry, false);
        }

        $this->_text = $buffer === '' ? [] : [$buffer];
    }

    private function encodeEntry(array $entry, bool $useNumericString): string
    {
        switch ($entry['type']) {
            case 'id3v1':
                return $useNumericString ? (string) $entry['value'] : '(' . $entry['value'] . ')';
            case 'special':
                return $useNumericString ? $entry['value'] : '(' . $entry['value'] . ')';
            default:
                $text = $entry['text'];
                if (!$useNumericString && str_starts_with($text, '(')) {
                    return '(' . $text;
                }

                return $text;
        }
    }

    private function findId3v1CodeByName(string $name): ?int
    {
        foreach (Id3v1::$genres as $code => $label) {
            if (!is_string($label)) {
                continue;
            }
            if (strcasecmp($label, $name) === 0) {
                return (int) $code;
            }
        }

        return null;
    }

    private function findSpecialCodeByName(string $name): ?string
    {
        foreach (self::SPECIAL_GENRES as $code => $label) {
            if (strcasecmp($label, $name) === 0) {
                return $code;
            }
        }

        return null;
    }
}
