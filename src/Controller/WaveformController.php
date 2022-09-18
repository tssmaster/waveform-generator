<?php

namespace App\Controller;

use App\Configuration\Config;
use App\Service\WaveformAnalyzer;
use App\Service\WaveformFileParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Extract information for conversations of different waveform channels.
 *
 * In real project waveform files can be stored in database and organized
 * in folders by categories, date, also add hash to file names to avoid dublicates.
 * We will consider all files to have extension 'wf', even though in the real project they may not have the extension at all.
 */
class WaveformController extends AbstractController
{
    private WaveformFileParser $waveformFileParser;

    private WaveformAnalyzer $waveformAnalyzer;

    /**
     * @param WaveformFileParser $waveformFileParser
     * @param WaveformAnalyzer $waveformAnalyzer
     */
    public function __construct(WaveformFileParser $waveformFileParser, WaveformAnalyzer $waveformAnalyzer)
    {
        $this->waveformFileParser = $waveformFileParser;
        $this->waveformAnalyzer = $waveformAnalyzer;
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $channelsData = [
            Config::USER_CHANNEL_NAME => $this->waveformFileParser->parseFile(Config::USER_CHANNEL_NAME),
            Config::CUSTOMER_CHANNEL_NAME => $this->waveformFileParser->parseFile(Config::CUSTOMER_CHANNEL_NAME)
        ];

        return new JsonResponse(
            [
                'longest_user_monologue' => $this->waveformAnalyzer->getLongestMonolog(Config::USER_CHANNEL_NAME, $channelsData),
                'longest_customer_monologue' => $this->waveformAnalyzer->getLongestMonolog(Config::CUSTOMER_CHANNEL_NAME, $channelsData),
                'user_talk_percentage' => $this->waveformAnalyzer->getConversationPercent(Config::USER_CHANNEL_NAME, $channelsData),
                'user' => $this->waveformAnalyzer->getConversations($channelsData[Config::USER_CHANNEL_NAME]),
                'customer' => $this->waveformAnalyzer->getConversations($channelsData[Config::CUSTOMER_CHANNEL_NAME])
            ]
        );
    }
}
