<?php

// *****************************************************************************
//
// Copyright (c) 2010 José Manuel, Gutiérrez de la Concha. All rights reserved.
//
// This copy of oz-web is licensed to you under the terms described
// in the LICENSE file included in this distribution.
//
// email: pepone.onrez@gmail.com
//
// *****************************************************************************

class DirectoryNotExistsException extends SystemException
{
    public function __construct($path)
    {
        parent::__construct('El directorio "' . $path . '" no existe.');
    }
}

class FileNotExistsException extends SystemException
{
    public function __construct($path)
    {
        parent::__construct('El fichero "' . $path . '" no existe.');
    }
}

class FileUtil
{
    static public function mkdir($dir, $mask = 0777, $recursive = false)
    {
        if(!is_dir($dir))
        {
            if(!mkdir($dir, $mask, $recursive))
            {
                throw new SystemException('Failed to create output directory: ' . $dir);
            }
        }
    }

    static public function dirEntries($source, $omitHidden = false)
    {
        $files = array();
        if(is_dir($source))
        {
            $dir = dir($source);
            while(false !== $entry = $dir->read())
            {
                if($entry == "." || $entry == "..")
                {
                    continue;
                }

                if($omitHidden)
                {
                    if(StringUtil::beginsWith($entry, '.'))
                    {
                        continue;
                    }
                }
                $f = $source;
                if(!StringUtil::endsWith($f, '/'))
                {
                    $f .= '/';
                }
                $f .= $entry;
                $files[] = $f;
            }
            // Clean up
            $dir->close();
        }
        return $files;
    }

    static public function rmdir($dir)
    {
        if(!file_exists($dir))
        {
            return false;
        }
        if(!is_dir($dir))
        {
            if(!unlink($dir))
            {
                return false;
            }
        }
        else
        {
            $items = FileUtil::dirEntries($dir);
            if(is_array($items))
            {
                foreach($items as $i)
                {
                    if($i == '.' || $i == '..')
                    {
                        continue;
                    }
                    FileUtil::rmdir($i);
                }
            }
            if(!rmdir($dir))
            {
                return false;
            }
        }
        return true;
    }

    static public function copy($source, $target)
    {
        if(is_file($source))
        {
            $c = copy($source, $target);
            chmod($target, 0777);
            return $c;
        }

        if(is_dir($source))
        {
            if(!StringUtil::endsWith($target, '/'))
            {
                $target .= '/';
            }
            $target .= FileUtil::fileName($source);
        }
        
        if(!is_dir($target))
        {
            error_log('Creating dir: "' . $target . '"');
            FileUtil::mkdir($target);
        }

        $dir = dir($source);
        while(false !== $entry = $dir->read())
        {
            if($entry == "." || $entry == "..")
            {
                continue;
            }

            if($target !== "$source/$entry")
            {
                FileUtil::copy("$source/$entry", $target);
            }
        }
        // Clean up
        $dir->close();
        return true;
    }

    static public function fileName($path)
    {
        $token = strrpos($path, '/');
        if($token !== false)
        {
            $path = substr($path, $token + 1);
        }
        return $path;
    }

    static public function dirName($path)
    {
        $token = strrpos($path, '/');
        if($token === false)
        {
            return '';
        }
        $len = strlen($path);
        $len = $len - ($len - $token);
        return substr($path, 0, $len);
    }

    static public function fileExtension($path)
    {
        $path = FileUtil::fileName($path);
        $token = strrpos($path, '.');
        if($token !== false)
        {
            $path = substr($path, $token);
        }
        return $path;
    }

    static public function baseName($path)
    {
        $path = FileUtil::fileName($path);
        $token = strrpos($path, '.');
        if($token !== false)
        {
            $len = strlen($path);
            $len = $len - ($len - $token);
            $path = substr($path, 0, $len);
        }
        return $path;
    }


    public static function formatBytes($b, $p = null)
    {
        /**
         *
         * @author Martin Sweeny
         * @version 2010.0617
         *
         * returns formatted number of bytes.
         * two parameters: the bytes and the precision (optional).
         * if no precision is set, function will determine clean
         * result automatically.
         *
         **/
        $units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
        $c = 0;
        if(!$p && $p !== 0)
        {
            foreach($units as $k => $u)
            {
                if(($b / pow(1024, $k)) >= 1)
                {
                    $r["bytes"] = $b / pow(1024,$k);
                    $r["units"] = $u;
                    $c++;
                }
            }
            return number_format($r["bytes"], 2) . " " . $r["units"];
        }
        else
        {
            return number_format($b / pow(1024, $p)) . " " . $units[$p];
        }
    }
}