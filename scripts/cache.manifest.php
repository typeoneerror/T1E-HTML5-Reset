<?php

/**
 * This is a PHP script that generates a cache manifest
 * for HTML5's offline support. Certain modern browsers support
 * a browser-based cache so you can view a site in offline mode.
 */

date_default_timezone_set('America/Los_Angeles');

// path to web assets
define('PATH', realpath(dirname(dirname(__FILE__)) . "/public"));
// file to output manifest to
define('FILE_TO_WRITE', PATH . "/cache.manifest");


// directories to list
// the key will be used as a comment in the generated manifest
$dirs = array(
    "CSS"        => "/css",
    "Javascript" => "/js",
    "Images"     => "/img",
);

// additional individual files
$additions = array(
    "/index.html",
);

// do not cache, relative to PATH
// for the NETWORK property
$network = array(
    "/.htaccess",
    "/robots.txt",
    "/cache.manifest", // for Firefox, I think
);

// fallback for offline mode
$fallback = "/offline.html";


ob_start();

function listDirectory($dir, $relativeTo = "")
{
    $array = array();
    if ($handle = @opendir($dir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if ($file != "." && $file != ".." && $file[0] != ".")
            {
                $path = "$dir/$file";
                if (!is_dir($path))
                {
                    array_push($array, $relativeTo . $file);
                }
                else
                {
                    $ls = listDirectory($path, $relativeTo . $file . "/");
                    $array = array_merge($array, $ls);
                }
            }
        }
        closedir($handle);
    }
    return $array;
}

// start output
echo "CACHE MANIFEST\n\n";

if (!empty($fallback))
{
    echo "FALLBACK:\n\n";
    echo "/ {$fallback}\n\n";
}

if (!empty($network))
{
    echo "NETWORK:\n\n";
    foreach ($network as $item)
    {
        echo "$item\n";
    }
    echo "\n";
}

if (!empty($dirs)) echo "CACHE:\n\n";

foreach ($dirs as $name => $dir)
{
    $ls = listDirectory(PATH . $dir);
    echo "# {$name}\n";
    foreach ($ls as $cache)
    {
        echo "$dir/$cache\n";
    }
    echo "\n";
}

if (!empty($additions))
{
    echo "\n# Additions\n";
    foreach ($additions as $addition)
    {
        echo "$addition\n";
    }
    echo "\n";
}

echo "# Generated: " . date("F d Y H:i:s", time()) . "\n";

$contents = ob_get_clean();
$fp = fopen(FILE_TO_WRITE, 'w');
fwrite($fp, $contents);
fclose($fp);

echo $contents;