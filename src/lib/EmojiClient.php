<?php

declare(strict_types=1);

namespace App\Lib;

use Emojione\Client;

class EmojiClient extends Client
{
    /**
     * This property is missing from the original Emojione\Client and causes a deprecation warning in PHP 8.2+.
     * We are declaring it here to make the code compliant with modern PHP.
     * @var string
     */
    public $imageType = 'png';

    /**
     * This property exists in the parent, but we re-declare it here to satisfy older code that might access it dynamically.
     * @var string
     */
    public $imagePathSVG;

    /**
     * This property exists in the parent, but we re-declare it here to satisfy older code that might access it dynamically.
     * @var bool
     */
    public $ascii;
}