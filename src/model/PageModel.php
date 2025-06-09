<?php

namespace DocPHT\Model;

use DocPHT\Core\Translator\T;

class PageModel
{
    const DB = 'data/pages.json';

    public function getHomePage()
    {
        $homepage_config_path = 'data/homepage.json';
        if (!file_exists($homepage_config_path)) return false;

        $s = file_get_contents($homepage_config_path);
        $config_data = json_decode($s, true);

        if (empty($config_data['path'])) return false;
        
        $slug = str_replace('.php', '', $config_data['path']);
        return $this->getPageBySlug($slug);
    }

    public function getPageBySlug(string $slug)
    {
        $data = $this->connect();
        if (is_null($data)) return false;

        foreach ($data as $item) {
            if (isset($item['pages']) && $item['pages']['slug'] === $slug) {
                $page = $item['pages'];
                return [
                    'id'           => $page['id'],
                    'title'        => str_replace('-', ' ', ucfirst($page['filename'])),
                    'path'         => 'pages' . DS . $page['topic'] . DS . $page['filename'] . '.php',
                    'is_published' => (bool) $page['published'],
                ];
            }
        }
        return false;
    }
    
    public function connect()
    {
        if(!file_exists(self::DB))
        {
            file_put_contents(self::DB,'[]');
        } 
        
        return json_decode(file_get_contents(self::DB),true);
    }
    
    public function create($topic, $filename)
    {
        $data = $this->connect();
        $id = uniqid('', true);
        $topic = strtolower(str_replace(' ', '-', pathinfo($topic, PATHINFO_FILENAME) ));
        $filename = strtolower(str_replace(' ', '-', pathinfo($filename, PATHINFO_FILENAME)));
        $slug = preg_replace('/[^a-z0-9-]+/i', '', trim(strtolower($topic))) .'/'. preg_replace('/[^a-z0-9-]+/i', '', trim(strtolower($filename)));

        if (!is_null($data)) {
            $slugs = $this->getAllFromKey('slug');
            if (is_array($slugs)) {
                if(in_array($slug, $slugs))
                {
                    $count = 1;
                    while(in_array(($slug . '-' . ++$count ), $slugs));
                    $slug = $slug . '-' . $count;
                    $filename = $filename . '-' . $count;
                }
            }
        }
        
        $phpPath = 'pages/'.$slug.'.php';
        $jsonPath = 'data/'.$slug.'.json';
        
        $data[] = [
            'pages' => [
                    'id' => $id,
                    'slug' => $slug,
                    'topic' => $topic,
                    'filename' => $filename,
                    'phppath' => $phpPath,
                    'jsonpath' => $jsonPath,
                    'published' => 0,
                    'home' => 0
            ]
        ];
            
        $this->disconnect(self::DB, $data);
        
        return $id;
    }

    public function getPagesByTopic($topic)
    {
        $data = $this->connect();
        $array = [];
        if (!is_null($data)) {
            foreach($data as $value){
                if($value['pages']['topic'] === $topic) {
                    $array[] = $value['pages'];
                }
            } 
            if(!empty($array)) {
                usort($array, fn($a, $b) => $a['filename'] <=> $b['filename']);
            }
            return $array;
        }
        return false;
    }

    public function getPublishedPagesByTopic($topic)
    {
        $data = $this->connect();
        $array = [];
        if (!is_null($data)) {
            foreach($data as $value){
                if($value['pages']['topic'] == $topic && $value['pages']['published'] == 1) {
                    $array[] = $value['pages'];
                }
            } 
            if (!empty($array)) {
                usort($array, fn($a, $b) => $a['filename'] <=> $b['filename']);
            }
            return $array;
        }
        return false;
    }

    public function getUniqTopics()
    {
        $array = $this->getAllFromKey('topic');
        if (is_array($array) && !empty($array)) {
            $array = array_unique($array);
            sort($array);
            return $array;
        }
        return false;
    }

    public function getUniqPublishedTopics()
    {
        $array = $this->getAllPublishedFromKey('topic');
        if (is_array($array) && !empty($array)) {
            $array = array_unique($array);
            sort($array);
            return $array;
        }
        return false;
    }
    
    public function getAllFromKey($key)
    {
        $data = $this->connect();
        $array = [];
        if (!is_null($data) && !empty($data)) {
            foreach($data as $value){
                if (isset($value['pages'][$key])) {
                    $array[] = $value['pages'][$key];
                }
            } 
            return $array;
        }
        return false;
    }
    
    public function getAllPublishedFromKey($key)
    {
        $data = $this->connect();
        $array = [];
        if (!is_null($data) && !empty($data)) {
            foreach($data as $value){
                if(isset($value['pages']['published']) && $value['pages']['published'] == 1 && isset($value['pages'][$key])) {
                    $array[] = $value['pages'][$key];
                }
            } 
            return $array;
        }
        return false;
    }

    public function disconnect($path, $data)
    {
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}