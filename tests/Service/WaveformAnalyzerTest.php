<?php

namespace App\Tests\Service;

use App\Configuration\Config;
use App\Service\WaveformAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * @covers WaveformAnalyzer
 */
class WaveformAnalyzerTest extends TestCase
{
    public function testGetConversations()
    {
        $channelData = [
            [
                'silence_start' => 1.12,
                'silence_end' => 2.234
            ],
            [
                'silence_start' => 3.14,
                'silence_end' => 7.891
            ],
            [
                'silence_start' => 18.92,
                'silence_end' => 12.745
            ]
        ];

        $waveformAnalyzer = new WaveformAnalyzer();

        $result = $waveformAnalyzer->getConversations($channelData);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertIsArray($result[0]);
        $this->assertEquals($result[0][0], 0);
        $this->assertEquals($result[0][1], 1.12);
        $this->assertEquals($result[1][0], 2.234);
        $this->assertEquals($result[1][1], 3.14);
    }

    /**
     * This is demo of simple conversation monolog test
     * In real project we can use more complex $channelsData with different channels to test overlapping dialogs
     */
    public function testGetLongestMonolog()
    {
        $channelId = Config::USER_CHANNEL_NAME;
        $channelsData = [
            Config::USER_CHANNEL_NAME => [
                [
                    'silence_start' => 1.12,
                    'silence_end' => 2.234
                ],
                [
                    'silence_start' => 3.14,
                    'silence_end' => 7.891
                ],
                [
                    'silence_start' => 18.92,
                    'silence_end' => 45.745
                ]
            ]
        ];

        $waveformAnalyzer = new WaveformAnalyzer();

        $result = $waveformAnalyzer->getLongestMonolog($channelId, $channelsData);

        $this->assertEquals(11.029, $result);
    }

    /**
     * This is demo of simple conversation percent
     * In real project we can use more complex $channelsData with different channels for more accurate test
     */
    public function testGetConversationPercent()
    {
        $channelId = Config::USER_CHANNEL_NAME;
        $channelsData = [
            Config::USER_CHANNEL_NAME => [
                [
                    'silence_start' => 1.12,
                    'silence_end' => 2.234
                ],
                [
                    'silence_start' => 3.14,
                    'silence_end' => 7.891
                ],
                [
                    'silence_start' => 18.92,
                    'silence_end' => 39.745
                ]
            ]
        ];

        $waveformAnalyzer = new WaveformAnalyzer();

        $result = $waveformAnalyzer->getConversationPercent($channelId, $channelsData);

        $this->assertEquals(32.85, $result);
    }
}