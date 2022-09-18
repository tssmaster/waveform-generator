<?php

namespace App\Tests\Service;

use App\Configuration\Config;
use App\Service\WaveformFileParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @covers \App\Controller\WaveformFileParser
 */
class WaveformFileParserTest extends TestCase
{
    public function testParseFile()
    {
        /** @var MockObject $containerMock */
        $containerMock = $this->createMock(ContainerBagInterface::class);

        $containerMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(dirname(__DIR__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$_ENV['APP_WAVEFORM_FILES_DIR']);

        /** @var MockObject|WaveformFileParser $waveformFileParserMock */
        $waveformFileParserMock = $this->getMockBuilder(WaveformFileParser::class)
            ->setConstructorArgs([
                $containerMock
            ])
            ->onlyMethods([])
            ->getMock();

        $data = $waveformFileParserMock->parseFile(Config::USER_CHANNEL_NAME);

        $this->assertIsArray($data);
        $this->assertIsArray($data[0]);
        $this->assertArrayHasKey(Config::SILENCE_START_MARKER, $data[0]);
        $this->assertArrayHasKey(Config::SILENCE_END_MARKER, $data[0]);
    }
}