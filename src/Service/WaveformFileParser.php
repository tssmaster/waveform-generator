<?php

namespace App\Service;

use Exception;
use InvalidArgumentException;

/**
 * Parse waveform files to extract conversation periods
 */
class WaveformFileParser
{
    public const SILENCE_START_MARKER = 'silence_start';

    public const SILENCE_END_MARKER = 'silence_end';

    /**
     * @param string $file
     * @return array
     */
    public function parseFile(string $file): array
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('%s does not exist', $file));
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        /**
         * We consider that every file start from 0 seconds,
         * but file can start with silence or with conversation, it depends on the first row
         */

        $parsedResponse = [];
        $silenceStart = null;

        foreach ($lines as $line) {
            $matches = null;
            $res = preg_match('/\[.*\][ ]*(' . self::SILENCE_START_MARKER . '|' . self::SILENCE_END_MARKER.'):[ ]*(\d+(?:\.\d+)?)/', $line, $matches);

            if (!$res || empty($matches[1]) || empty($matches[2])){
                throw new Exception('The file is corrupt or has invalid data');
            }

            $marker = $matches[1];
            $markerValue = $matches[2];

            // First row parsing
            if ($silenceStart === null) {
                if ($marker == self::SILENCE_START_MARKER) {
                    $silenceStart = $markerValue;
                } else {
                    $parsedResponse[] = [0, $markerValue];
                    $silenceStart = false;
                }
            } else {
                if ($silenceStart !== false) {
                    if ($marker != self::SILENCE_END_MARKER) {
                        throw new Exception('The file has invalid data, there are more than one silence_start consecutive markers');
                    }
                    $parsedResponse[] = [$silenceStart, $markerValue];
                    $silenceStart = false;
                } else {
                    if ($marker != self::SILENCE_START_MARKER) {
                        throw new Exception('The file has invalid data, there are more than one silence_end consecutive markers');
                    }
                    $silenceStart = $markerValue;
                }
            }
        }

        return $parsedResponse;
    }
}
