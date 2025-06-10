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

use App\Model\PageModel;
use App\Core\Helper\TextHelper;
use App\Core\Translations\T;
use Flasher\Prime\FlasherInterface;

class DocBuilder 
{
    protected PageModel $pageModel;
    public ?FlasherInterface $flasher;
    
    public function __construct()
    {
        $this->pageModel = new PageModel();
        
        // This is the fix: Use the central Flasher service created in index.php
        if (isset($GLOBALS['flasher'])) {
            $this->flasher = $GLOBALS['flasher'];
        } else {
            $this->flasher = null;
        }
    }

    /**
     * jsonSwitch
     *
     * @param array $jsonVals
     *
     * @return string
     */
    public function jsonSwitch(array $jsonVals): string
    {
        $option = '';
        if (isset($jsonVals['key'])) {
            switch ($jsonVals['key']) {
                case 'title':
                    $option = $this->title($jsonVals['v1'], $jsonVals['v1']);
                    break;
                case 'description':
                    $option = $this->description($jsonVals['v1']);
                    break;
                case 'pathAdd':
                case 'path':
                    $option = $this->pathAdd($jsonVals['v1']);
                    break;
                case 'codeInline':
                    $option = $this->codeInline($jsonVals['v1'], $jsonVals['v2']);
                    break;
                case 'codeFile':
                    $option = $this->codeFile($jsonVals['v1'], $jsonVals['v2']);
                    break;
                case 'blockquote':
                    $option = $this->blockquote($jsonVals['v1']);
                    break;
                case 'image':
                    $option = $this->image($jsonVals['v1'], $jsonVals['v2']);
                    break;
                case 'imageURL':
                    $option = $this->imageURL($jsonVals['v1'], $jsonVals['v2']);
                    break;
                case 'linkButton':
                    $option = $this->linkButton($jsonVals['v1'], $jsonVals['v2'], $jsonVals['v3']);
                    break;
                case 'markdown':
                    $option = $this->markdown($jsonVals['v1']);
                    break;
                case 'markdownFile':
                    $option = $this->markdownFile($jsonVals['v1']);
                    break;
                case 'addButton':
                    $option = '$html->addButton(),' . "\n";
                    break;
            }
        }
        return $option;
    }
    
    /**
     * valuesToArray
     *
     * @param  array $values
     * @param  string|null $file_path
     * @param  array $self
     *
     * @return array
     */
    public function valuesToArray(array $values, ?string $file_path = null, array $self = []): array
    {
        $option = $self;
        if (isset($values['options'])) {
            switch ($values['options']) {
                case 'title':
                case 'description':
                case 'pathAdd':
                case 'blockquote':
                case 'markdown':
                    $option = ['key' => $values['options'], 'v1' => $values['option_content'], 'v2' => '', 'v3' => '', 'v4' => ''];
                    break;
                case 'codeInline':
                    $option = ['key' => $values['options'], 'v1' => $values['option_content'], 'v2' => $values['language'], 'v3' => '', 'v4' => ''];
                    break;
                case 'codeFile':
                    $option = ['key' => $values['options'], 'v1' => substr((string) $file_path, 5), 'v2' => $values['language'], 'v3' => '', 'v4' => ''];
                    break;
                case 'image':
                    $option = ['key' => $values['options'], 'v1' => substr((string) $file_path, 5), 'v2' => $values['option_content'], 'v3' => '', 'v4' => ''];
                    break;
                case 'imageURL':
                    $option = ['key' => $values['options'], 'v1' => $values['option_content'], 'v2' => $values['names'], 'v3' => '', 'v4' => ''];
                    break;
                case 'linkButton':
                    $option = ['key' => $values['options'], 'v1' => $values['option_content'], 'v2' => $values['names'], 'v3' => $values['trgs'], 'v4' => ''];
                    break;
                case 'markdownFile':
                    $option = ['key' => $values['options'], 'v1' => substr((string) $file_path, 5), 'v2' => '', 'v3' => '', 'v4' => ''];
                    break;
                case 'addButton':
                    $option = ['key' => 'addButton', 'v1' => '', 'v2' => '', 'v3' => '', 'v4' => ''];
                    break;
            }
        }
        return $option;
    }   
    
    /**
     * removeOldFile
     *
     * @param  string $key1
     * @param  string $key2
     * @param  string $path
     *
     */
    public function removeOldFile(string $key1, string $key2, string $path): void
    {
        if ($key1 === 'image' && $key2 !== 'image' && file_exists($path)) {
            unlink($path);
        }
        if ($key1 === 'codeFile' && $key2 !== 'codeFile' && file_exists($path)) {
            unlink($path);
        }
        if ($key1 === 'markdownFile' && $key2 !== 'markdownFile' && file_exists($path)) {
            unlink($path);
        }
    }
    
    /**
     * buildPhpPage
     *
     * @param  string $id
     *
     */
    public function buildPhpPage(string $id): void
    {
        $data = $this->pageModel->getPageData($id);
        $path = $this->pageModel->getPhpPath($id);
        $anchors = [];
        $values = [];
        
        foreach ($data as $vals) {
            $values[] = $this->jsonSwitch($vals);
            if ($vals['key'] === "title") {
                $anchors[] = "'" . TextHelper::e($vals['v1']) . "'";
            }
        }
            
        $file = "<?php\n\n"
                . "use App\Lib\DocPHT;\n\n"
                . '$_SESSION' . "['page_id'] = '" . $id . "';\n\n"
                . '$html = new DocPHT([' . implode(',', $anchors) . "]);\n"
                . '$values' . " = [\n" . implode('', $values) . '$html->addButton(),' . "\n" . "];";
        
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $file);
    }
    
    /**
     * startsWith
     *
     * @param  string $haystack
     * @param  string $needle
     *
     * @return bool
     */
    public function startsWith(string $haystack, string $needle): bool
    {
         return str_starts_with($haystack, $needle);
    }   

    /**
    * datetimeNow
    *
    * @return string
    */
    public static function datetimeNow(): string 
    {
        $timeZone = new \DateTimeZone(defined('TIMEZONE') ? TIMEZONE : 'UTC');
        $datetime = new \DateTime('now', $timeZone);
        return $datetime->format(defined('DATAFORMAT') ? DATAFORMAT : 'Y-m-d H:i:s');
    }

    /**
     * setFolderPermissions
     *
     * @param  string $folder
     *
     * @return void
     */
    public function setFolderPermissions(string $folder): void
    {
        $dirpath = $folder;
        $dirperm = 0755;
        $fileperm = 0644; 
        if (is_dir($dirpath)) {
            @chmod($dirpath, $dirperm);
            $glob = glob($dirpath . "/*");
            if ($glob) {
                foreach ($glob as $ch) {
                    @is_dir($ch) ? @chmod($ch, $dirperm) : @chmod($ch, $fileperm);
                }
            }
        }
    }
    
    /**
     * upload
     *
     * @param  \Nette\Http\FileUpload|null $file
     * @param  string $path
     *
     * @return string
     */
    public function upload(?\Nette\Http\FileUpload $file, string $path): string
    {
        if ($file && $file->isOk()) {
            $file_path = 'data/' . substr(pathinfo($path, PATHINFO_DIRNAME), 5) . '/' . uniqid() . '_' . $file->getSanitizedName();
            $file->move($file_path);
            return $file_path;
        }
        return '';
    }

    /**
     * uploadNoUniqid
     *
     * @param  \Nette\Http\FileUpload|null $file
     * @param  string $path
     *
     * @return string
     */
    public function uploadNoUniqid(?\Nette\Http\FileUpload $file, string $path): string
    {
        if ($file && $file->isOk()) {
            $file_path = 'data/' . substr(pathinfo($path, PATHINFO_DIRNAME), 5) . '/' . $file->getSanitizedName();
            $file->move($file_path);
            return $file_path;
        }
        return '';
    }

    /**
     * uploadLogoDocPHT
     *
     * @param  \Nette\Http\FileUpload|null $file
     *
     * @return string
     */
    public function uploadLogoDocPHT(?\Nette\Http\FileUpload $file): string
    {
        if ($file && $file->isOk()) {
            $file_path = 'data/logo.png';
            $file->move($file_path);
            return $file_path;
        }
        return '';
    }

    /**
     * uploadFavDocPHT
     *
     * @param  \Nette\Http\FileUpload|null $file
     *
     * @return string
     */
    public function uploadFavDocPHT(?\Nette\Http\FileUpload $file): string
    {
        if ($file && $file->isOk()) {
            $file_path = 'data/favicon.png';
            $file->move($file_path);
            return $file_path;
        }
        return '';
    }
    
    /**
     * uploadBackup
     *
     * @param  \Nette\Http\FileUpload|null $file
     *
     * @return string
     */
    public function uploadBackup(?\Nette\Http\FileUpload $file): string
    {
        if ($file && $file->isOk()) {
            $file_path = 'data/' . $file->getSanitizedName();
            $file->move($file_path);
            return $file_path;
        }
        return '';
    }
    
    /**
     * checkImportVersion
     *
     * @param  string $file_path
     * @param  string $path
     *
     * @return bool
     */
    public function checkImportVersion(string $file_path, string $path): bool
    {
        $zipData = new \ZipArchive(); 
        if ($zipData->open($file_path) === true) {
            $check = $zipData->locateName($path) !== false;
            $zipData->close();
            return $check;
        }
        return false;
    }
    
    /**
     * title
     *
     * @param  string $val
     * @param  string $anch
     *
     * @return string
     */
    public function title(string $val, string $anch): string
    {
        $val = TextHelper::e($val);
        $anch = TextHelper::e($anch);
        return '$html->title' . "('{$val}','{$anch}'), \n";
    }
    
    /**
     * description
     *
     * @param  string $val
     *
     * @return string
     */
    public function description(string $val): string
    {
        $val = TextHelper::e($val);
        return '$html->description' . "('{$val}'), \n";
    }
    
    /**
     * path
     *
     * @param  string $val
     * @param  string $ext
     *
     * @return string
     */
    public function path(string $val, string $ext): string
    {
        $val = TextHelper::e($val);
        $ext = TextHelper::e($ext);
        return '$html->path' . "('pages/{$val}.{$ext}'), \n";
    }

    /**
     * pathHome
     *
     * @param  string $val
     * @param  string $ext
     *
     * @return string
     */
    public function pathHome(string $val, string $ext): string
    {
        $val = TextHelper::e($val);
        $ext = TextHelper::e($ext);
        return '$html->path' . "('data/{$val}.{$ext}'), \n";
    }
    
    /**
     * pathAdd
     *
     * @param  string $val
     *
     * @return string
     */
    public function pathAdd(string $val): string
    {
        $val = TextHelper::e($val);
        return '$html->path' . "('{$val}'), \n";
    }
    
    /**
     * codeInline
     *
     * @param  string $val
     * @param  string $lan
     *
     * @return string
     */
    public function codeInline(string $val, string $lan): string
    {
        $val = addcslashes($val, "\'");
        return '$html->codeInline' . "('{$lan}','{$val}'), \n";
    }
    
    /**
     * codeFile
     *
     * @param  string $src
     * @param  string $lan
     *
     * @return string|void
     */
    public function codeFile(string $src, string $lan)
    {
        if (!empty($src)) {
            $src = addcslashes($src, "\'");
            return '$html->codeFile' . "('{$lan}','{$src}'), \n";
        }
        
        $this->flasher?->addError(T::trans('No files added for uploading'));
    }
    
    /**
     * blockquote
     *
     * @param  string $val
     *
     * @return string
     */
    public function blockquote(string $val): string
    {
        $val = TextHelper::e($val);
        return '$html->blockquote' . "('{$val}'), \n";
    }
    
    /**
     * image
     *
     * @param  string $src
     * @param  string $val
     *
     * @return string
     */
    public function image(string $src, string $val): string
    {
        $val = TextHelper::e($val);
        $src = TextHelper::e($src);
        return '$html->image' . "('{$src}','{$val}'), \n";
    }
    
    /**
     * imageURL
     *
     * @param  string $src
     * @param  string $val
     *
     * @return string
     */
    public function imageURL(string $src, string $val): string
    {
        $val = TextHelper::e($val);
        $src = TextHelper::e($src);
        return '$html->imageURL' . "('{$src}','{$val}'), \n";
    }
    
    /**
     * markdown
     *
     * @param  string $val
     *
     * @return string
     */
    public function markdown(string $val): string
    {
        $val = addcslashes($val, "\'");
        return '$html->markdown' . "('{$val}'), \n";
    }
    
    /**
     * markdownFile
     *
     * @param  string $src
     *
     * @return string|void
     */
    public function markdownFile(string $src)
    {
        if (!empty($src)) {
            $src = addcslashes($src, "\'");
            return '$html->markdownFile' . "('{$src}'), \n";
        }
        
        $this->flasher?->addError(T::trans('No files added for uploading'));
    }
    
    /**
     * linkButton
     *
     * @param  string $src
     * @param  string $val
     * @param  string $trg
     *
     * @return string
     */
    public function linkButton(string $src, string $val, string $trg): string
    {
        $val = TextHelper::e($val);
        $src = TextHelper::e($src);
        return '$html->linkButton' . "('{$src}','{$val}','{$trg}'), \n";
    }
    
    /**
     * getOptions
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'title' => T::trans('Add title'),
            'description' => T::trans('Add description'),
            'pathAdd'  => T::trans('Add path'),
            'codeInline' => T::trans('Add code inline'),
            'codeFile' => T::trans('Add code from file'),
            'blockquote' => T::trans('Add blockquote'),
            'image' => T::trans('Add image from file'),
            'imageURL' => T::trans('Add image from url'),
            'markdown' => T::trans('Add markdown'),
            'markdownFile' => T::trans('Add markdown from file'),
            'linkButton' => T::trans('Add link button')
        ];
    }

    /**
     * listCodeLanguages
     *
     * @return array
     */
    public function listCodeLanguages(): array
    {
        $json = file_get_contents(__DIR__ . '/../Translations/code-translations.json');
        return json_decode($json, true);
    }
}