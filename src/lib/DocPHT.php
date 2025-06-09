<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Lib;

use ParsedownCheckbox;
use Emojione\Client;
use Emojione\Ruleset;
use DocPHT\Core\Translator\T;

class DocPHT extends DocBuilder
{
    protected $client;
    protected $parsedown;
    protected $parsedowncheckbox;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->parsedown = new \Parsedown();
        $this->parsedowncheckbox = new ParsedownCheckbox();
        $this->client = new Client(new Ruleset());
        $this->client->imageType = 'svg';
        $this->client->imagePathPNG = '';
        $this->client->imagePathSVG = 'public/assets/emojione/svg/';
        $this->client->imagePathSVGSprites = '';
        $this->client->ascii = true;
        $this->client->unicodeAlt = false;
        $this->client->shortnameConversion = true;
    }
    
    /**
     * get
     *
     * @param  mixed $key
     * @param  mixed $val
     *
     * @return void
     */
    public function get($key, $val = '')
    {
        $this->pages = $this->pageModel->getPages();
        $this->version = $this->versionModel->get();
        return $this->{$key}($val);
    }
    
    /**
     * anchorLinks
     *
     * @param  mixed $html
     *
     * @return void
     */
    public function anchorLinks($html)
    {
        $this->parsedown->options['header-attributes'] = function($text, $level) {
            $slug = preg_replace('/[^\w\d\x{0080}-\x{FFFF}]+/u', '-', strtolower(trim(strip_tags($text))));
            return ['id' => $slug, 'class' => 'anchor'];
        };
        $this->parsedown->options['html-transform'] = function($html) {
            $dom = new \DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
            libxml_use_internal_errors(false);
            $headings = [];
            foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $h) {
                foreach ($dom->getElementsByTagName($h) as $heading) {
                    $headings[] = $heading;
                }
            }
            foreach ($headings as $heading) {
                $link = $dom->createElement('a');
                $link->setAttribute('href', '#'.$heading->getAttribute('id'));
                $link->setAttribute('class', 'anchor-link');
                $link->nodeValue = '#';
                $heading->appendChild($link);
            }
            return preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
        };
    }
    
    /**
     * parse
     *
     * @param  mixed $val
     *
     * @return void
     */
    protected function parse($val) 
    {
        $this->anchorLinks($this->parsedowncheckbox->text($val));
        return $this->client->toImage($this->parsedown->text($this->parsedowncheckbox->text($val)));
    }
    
    /**
     * menu
     *
     * @return string
     */
    protected function menu()
    {
        return $this->doc->get('menu');
    }
    
    /**
     * tree
     *
     * @return string
     */
    protected function tree()
    {
        return $this->doc->get('tree');
    }

    /**
     * breadcrumb
     *
     * @return string
     */
    protected function breadcrumb()
    {
        return $this->doc->get('breadcrumb');
    }
    
    /**
     * search
     *
     * @param  mixed $val
     *
     * @return string
     */
    protected function search($val)
    {
        return $this->doc->get('search', $val);
    }
    
    /**
     * selectTranslations
     *
     * @param  mixed $val
     *
     * @return string
     */
    protected function selectTranslations($val = null)
    {
        return $this->doc->get('selectTranslations', $val);
    }

}