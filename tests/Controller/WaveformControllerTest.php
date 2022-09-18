<?php

namespace App\Tests\Controller;

use App\Controller\WaveformController;
use App\Service\WaveformAnalyzer;
use App\Service\WaveformFileParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @covers \App\Controller\WaveformController
 */
class WaveformControllerTest extends TestCase
{
    public function testIndex()
    {
        $silenceDataUser = [
            [1.12, 2.234], [3.14, 7.891], [18.92, 12.745]
        ];

        $silenceDataCustomer = [
            [4.12, 5.234], [8.24, 18.212], [38.92, 42.12]
        ];

        $conversationsDataUser = [
            [2.21, 3.234], [6.342, 9.12], [12.234, 18.537]
        ];

        $conversationsDataCustomer = [
            [2.73, 7.41], [12.55, 15.912], [28.14, 38.122]
        ];

        $longestUserMonolog = 12.35;
        $longestCustomerMonolog = 45.89;
        $conversationPercent = 43.57;

        /** @var MockObject $waveformFileParserMock */
        $waveformFileParserMock = $this->createMock(WaveformFileParser::class);

        $waveformFileParserMock
            ->expects($this->exactly(2))
            ->method('parseFile')
            ->willReturn($silenceDataUser, $silenceDataCustomer);

        /** @var MockObject $waveformAnalyzerMock */
        $waveformAnalyzerMock = $this->createMock(WaveformAnalyzer::class);

        $waveformAnalyzerMock
            ->expects($this->exactly(2))
            ->method('getLongestMonolog')
            ->willReturn($longestUserMonolog, $longestCustomerMonolog);

        $waveformAnalyzerMock
            ->expects($this->once())
            ->method('getConversationPercent')
            ->willReturn($conversationPercent);

        $waveformAnalyzerMock
            ->expects($this->exactly(2))
            ->method('getConversations')
            ->willReturn($conversationsDataUser, $conversationsDataCustomer);

        /** @var MockObject|WaveformController $waveformControllerMock */
        $waveformControllerMock = $this->getMockBuilder(WaveformController::class)
            ->setConstructorArgs([
                $waveformFileParserMock,
                $waveformAnalyzerMock
            ])
            ->onlyMethods([])
            ->getMock();

        $result = $waveformControllerMock->index();

        $this->assertInstanceOf(JsonResponse::class, $result);

        $data = json_decode($result->getContent(), true);

        $this->assertEquals($longestUserMonolog, $data['longest_user_monologue']);
        $this->assertEquals($longestCustomerMonolog, $data['longest_customer_monologue']);
        $this->assertEquals($longestCustomerMonolog, $data['longest_customer_monologue']);
        $this->assertEquals($conversationPercent, $data['user_talk_percentage']);
        $this->assertEquals($conversationsDataUser, $data['user']);
        $this->assertEquals($conversationsDataCustomer, $data['customer']);
    }
}