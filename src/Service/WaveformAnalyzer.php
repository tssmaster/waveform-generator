<?php

namespace App\Service;

use App\Configuration\Config;
use InvalidArgumentException;

/**
 * Analyze waveform data and extract conversations, longest monolog, talk percentage
 */
class WaveformAnalyzer
{
    /**
     * Calculate periods of conversation for given channel data.
     *
     * @param array $channelsData
     * @return array
     */
    public function getConversations(array $channelData): array
    {
        $conversationPeriods = [];
        $conversationStart = false;

        if (empty($channelData)) {
            return [];
        }

        $firstSilence = reset($channelData);

        // Handle first conversation
        if ($firstSilence[Config::SILENCE_START_MARKER] > 0) {
            $conversationPeriods[] = [0, $firstSilence[Config::SILENCE_START_MARKER]];
        }

        foreach ($channelData as $data) {
            if ($conversationStart === false) {
                $conversationStart = $data[Config::SILENCE_END_MARKER];
            } else {
                $conversationPeriods[] = [$conversationStart, $data[Config::SILENCE_START_MARKER]];
                $conversationStart = $data[Config::SILENCE_END_MARKER];
            }
        }

        return $conversationPeriods;
    }

    /**
     * Find longest channel monolog.
     * Method analyze whether there is conversations overlap in all channels to detect monologs.
     *
     * @param string $channelId
     * @param array $channelsData
     * @return float|null
     */
    public function getLongestMonolog(string $channelId, array $channelsData): ?float
    {
        if (!isset($channelsData[$channelId])) {
            throw new InvalidArgumentException(sprintf('Channel %s does not exist in channels data', $channelId));
        }

        $conversations = $this->getConversations($channelsData[$channelId]);

        $monologs = [];

        foreach ($conversations as $period) {
            if (!$this->hasConversationOverlap($period, $channelsData, $channelId)) {
                list($conversationStart, $conversationEnd) = $period;

                $monologs[] = round($conversationEnd - $conversationStart, 3);
            }
        }

        return !empty($monologs) ? max($monologs) : null;
    }

    /**
     * Calculate percent of user talk relative to entire call duration
     * which is max duration of all channels
     *
     * @param string $channelId
     * @param array $channelsData
     * @return float
     */
    public function getConversationPercent(string $channelId, array $channelsData): float
    {
        if (!isset($channelsData[$channelId])) {
            throw new InvalidArgumentException(sprintf('Channel %s does not exist in channels data', $channelId));
        }

        $totalCallDuration = $this->getTotalCallDuration($channelsData);

        $channelConversationDuration = $this->getConversationDuration($channelsData[$channelId]);

        return round(($channelConversationDuration / $totalCallDuration) * 100, 2);
    }

    /**
     * Analyze if there are conversation overlap in all other channels except $skipChannelId
     * In real project the skipped channel can be also array of channels
     * Return true if there is overlap with conversations in other channels
     *
     * @param array $period
     * @param array $channelsData
     * @param string $skipChannelId
     * @return boolean
     */
    private function hasConversationOverlap(array $period, array $channelsData, string $skipChannelId): bool
    {
        list($conversationStart, $conversationEnd) = $period;

        foreach($channelsData as $channelId => $channelData) {
            if ($channelId == $skipChannelId) {
                continue;
            }

            $conversations = $this->getConversations($channelData);

            foreach($conversations as $conversation) {
                list($coversationOtherChannelStart, $conversationOtherChannelEnd) = $conversation;

                if (
                    $conversationStart >= $coversationOtherChannelStart && $conversationStart <= $conversationOtherChannelEnd
                    || $conversationEnd >= $coversationOtherChannelStart && $conversationEnd <= $conversationOtherChannelEnd
                    || $conversationStart >= $coversationOtherChannelStart && $conversationEnd <= $conversationOtherChannelEnd
                    || $conversationStart <= $coversationOtherChannelStart && $conversationEnd >= $conversationOtherChannelEnd
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Calculate total call duration based on all channels
     *
     * @param array $channelsData
     * @return float
     */
    private function getTotalCallDuration(array $channelsData): float
    {
        $conversationDurations = [];

        foreach ($channelsData as $channel) {
            if (empty($channel)) {
                continue;
            }

            $conversationDurations[] = end($channel)[Config::SILENCE_END_MARKER];
        }

        if (empty($conversationDurations)) {
            return 0;
        }

        return max($conversationDurations);
    }

    /**
     * Calculate total conversation duration for given channel data
     *
     * @param array $channelData
     * @return float
     */
    private function getConversationDuration(array $channelData): float
    {
        $channelConversations = $this->getConversations($channelData);
        $channelConversationDuration = 0;

        foreach ($channelConversations as $period) {
            list($periodStart, $periodEnd) = $period;

            $channelConversationDuration += $periodEnd - $periodStart;
        }

        return $channelConversationDuration;
    }
}
