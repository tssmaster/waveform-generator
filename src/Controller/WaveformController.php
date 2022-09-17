<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class WaveformController extends AbstractController
{
    public function index(): JsonResponse
    {
        $waveformFiles = $this->getParameter('waveform_files');

        dd($waveformFiles);

        return new JsonResponse(
            ['code' => 0]
        );
    }
}
