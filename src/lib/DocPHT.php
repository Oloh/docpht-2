<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Lib;

use App\Core\Controller\BaseController;
use Emojione\Ruleset;
use ParsedownCheckbox;

class DocPHT extends BaseController
{
    protected object $parsedown;
    protected object $parsedowncheckbox;
    protected EmojiClient $client;
    public array $anchors = [];

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(array $anchors = [])
    {
        parent::__construct();
        $this->parsedown = new \Parsedown();
        $this->parsedowncheckbox = new ParsedownCheckbox();
        
        // This now uses our new, corrected EmojiClient
        $this->client = new EmojiClient(new Ruleset());
        $this->client->imageType = 'svg';
        $this->client->imagePathSVG = 'public/assets/emojione/svg/';
        $this->client->ascii = true;
        $this->anchors = $anchors;
    }
}