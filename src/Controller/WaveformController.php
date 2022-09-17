<?php

namespace App\Controller;

use App\Service\WaveformFileParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class WaveformController extends AbstractController
{
    /**
     * In real project waveform files can be stored in database and organized
     * in folders by categories, date, also add hash to file names to avoid dublicates
     */
    private const USER_CHANNEL_FILE_NAME = 'user_channel.wf';

    private const CUSTOMER_CHANNEL_FILE_NAME = 'customer_channel.wf';

    private WaveformFileParser $waveformFileParser;

    public function __construct(WaveformFileParser $waveformFileParser)
    {
        $this->waveformFileParser = $waveformFileParser;
    }

    public function index(): JsonResponse
    {
        $waveformFilesDir = $this->getParameter('waveform_files');

        $userFile = $this->waveformFileParser->parseFile($waveformFilesDir.DIRECTORY_SEPARATOR.self::USER_CHANNEL_FILE_NAME);

        dd($userFile);

        $customerFile = $this->waveformFileParser->parseFile($waveformFilesDir.DIRECTORY_SEPARATOR.self::CUSTOMER_CHANNEL_FILE_NAME);



        return new JsonResponse(
            ['code' => 0]
        );
    }
}
