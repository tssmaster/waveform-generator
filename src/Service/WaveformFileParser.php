<?php

namespace App\Service;

use App\Configuration\Config;
use Exception;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Parse waveform files to extract silence periods
 */
class WaveformFileParser
{
    private ContainerBagInterface $container;

    /**
     * @param ContainerBagInterface $container
     */
    public function __construct(ContainerBagInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Parse waveform file and return array of all silence periods
     *
     * @param string $channel
     * @return array
     */
    public function parseFile(string $channel): array
    {
        $file = $this->getParseFilePath($channel);

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

        foreach ($lines as $index => $line) {
            $matches = null;
            $res = preg_match('/\[.*\][ ]*(' . Config::SILENCE_START_MARKER . '|' . Config::SILENCE_END_MARKER.'):[ ]*(\d+(?:\.\d+)?)/', $line, $matches);

            if (!$res || empty($matches[1]) || empty($matches[2])) {
                throw new Exception('The file is corrupt or has invalid data');
            }

            $marker = $matches[1];
            $markerValue = $matches[2];

            // First row parsing
            if ($silenceStart === null) {
                if ($marker == Config::SILENCE_START_MARKER) {
                    $silenceStart = $markerValue;
                } else {
                    $this->pushSilencePeriod($parsedResponse, 0, $markerValue);
                    $silenceStart = false;
                }
            } else {
                if ($silenceStart !== false) {
                    if ($marker != Config::SILENCE_END_MARKER) {
                        throw new Exception('The file has invalid data, there are more than one silence_start consecutive markers');
                    }
                    
                    $this->pushSilencePeriod($parsedResponse, $silenceStart, $markerValue);

                    $silenceStart = false;
                } else {
                    if ($marker != Config::SILENCE_START_MARKER) {
                        throw new Exception('The file has invalid data, there are more than one silence_end consecutive markers');
                    }
                    $silenceStart = $markerValue;
                }
            }

            /**
             * Handle last line if it's silence_start, we will consider that the conversation stops immediately,
             * but we need this marker in final response to calculate duration of the last conversation
             */
            if ($index == count($lines) - 1 && $marker == Config::SILENCE_START_MARKER) {
                $this->pushSilencePeriod($parsedResponse, $markerValue, $markerValue);
            }
        }

        return $parsedResponse;
    }

    /**
     * @param string $channel
     * @return string
     */
    private function getParseFilePath(string $channel): string
    {
        return $this->container->get('waveform_files_dir') . DIRECTORY_SEPARATOR . $channel . '.' . Config::CHANNEL_FILE_EXT;
    }

    /**
     * @param array $periods 
     * @param float $silenceStart 
     * @param float $silenceEnd 
     * @return void 
     */
    private function pushSilencePeriod(array & $periods, float $silenceStart, float $silenceEnd): void
    {
        $periods[] = [
            Config::SILENCE_START_MARKER => $silenceStart,
            Config::SILENCE_END_MARKER => $silenceEnd
        ];
        
    }
}
