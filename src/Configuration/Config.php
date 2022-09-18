<?php

namespace App\Configuration;

/**
 * Definition of configuration constants
 */
abstract class Config
{
    public const USER_CHANNEL_NAME = 'user_channel';

    public const CUSTOMER_CHANNEL_NAME = 'customer_channel';

    public const CHANNEL_FILE_EXT = 'wf';

    public const SILENCE_START_MARKER = 'silence_start';

    public const SILENCE_END_MARKER = 'silence_end';
}
