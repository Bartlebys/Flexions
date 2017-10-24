<?php

require_once FLEXIONS_ROOT_DIR . '/flexions/core/Flog.php';
require_once FLEXIONS_ROOT_DIR . '/flexions/core/Hypotypose.php';
require_once FLEXIONS_ROOT_DIR . '/flexions/core/Flexed.php';


////////////////////////////////////
// FUNCTIONS
/////////////////////////////////////


/**
 * @param string $dir
 * @param array $result
 * @return array  of path
 */
function directoryToArray($dir, &$result = array()) {
    if (!file_exists($dir)) {
        $c = "OK";
    }
    $dirList = scandir($dir);
    foreach ($dirList as $key => $value) {
        $dotPos = strpos($value, '.');
        if (($dotPos === false) or ($dotPos != 0)) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $sub = directoryToArray($dir . DIRECTORY_SEPARATOR . $value);
                $result = array_merge($result, $sub);
            } else {
                $result [] = $dir . DIRECTORY_SEPARATOR . $value;
            }
        }
    }
    return $result;
}



/**
 * Delete all the files of a directory and the directory it self
 * @param $directoryPath
 * @return bool
 */
function deleteDirectory($directoryPath) {
    $files = array_diff(scandir($directoryPath), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$directoryPath/$file")) ? deleteDirectory("$directoryPath/$file") : unlink("$directoryPath/$file");
    }
    return rmdir($directoryPath);
}


/**
 * Logs a message
 * @param $message
 * @param bool $show
 */
function fLog($message, $show = false) {
    Flog::Instance()->addMessage($message);
    if ($show || ECHO_LOGS === true) {
        echo $message;
    }
}



/**
 * End of Lines
 * @param int $n
 * @return string
 */
function cr($n = 1) {
    $cr='';
    if(defined('COMMANDLINE_MODE')){
        $r= COMMANDLINE_MODE ? "\n" : "</br>";
    }else{
        $r="\n";
    }
    for ($i = 0; $i < $n; $i++) {
        $cr .= $r;
    }
    return $cr;
}

/**
 *
 * @param int $n
 * @return string
 */
function tabs($n = 1) {
    $s = "";
    for ($i = 0; $i < $n; $i++) {
        $s .= chr(9);
    }
    return $s;
}


/**
 *
 * @param string $string
 * @param int $n
 */
function echoIndent($string, $n = 1) {
    echo(stringIndent($string, $n));
}

function stringIndent($string, $n = 1) {
    if ($string == '') {
        return tabs($n) . $string;
    }
    $newString = '';
    $lines = explode(cr(1), $string);
    foreach ($lines as $line) {
        if ($line !== '') {
            $newString .= tabs($n) . $line;
            $newString .= cr(1);
        }
    }
    return $newString;
}


function stringFromFile($path){
    if (file_exists($path)) {
        ob_start();
        include $path;
        return ob_get_clean();
    }else{
        return 'UNEXISTING_FILE:'.$path;
    }
}


/**
 * Logs a warning
 * @param string $warning
 */
function fWarning($warning) {
    fLog('WARNING : ' . $warning, false);
}

function fDate() {
    $dt = new DateTime();
    return $dt->format('Y-m-d-') . microtime(true);
}


function hypotyposeToFiles() {
    $h = Hypotypose::Instance();

    // Delete the export path.
    @rmdir($h->exportFolderPath);

    $history = array();
    foreach ($h->flexedList as $f) {
        $path = $f->packagePath . $f->fileName;
        // We put to file once only per destination
        if (in_array($path, $history) == false) {

            $shouldBeExcluded = false;
            foreach ($h->excludePath as $pathToExclude) {
                if (strpos($path, $pathToExclude) !== false) {
                    $shouldBeExcluded = true;
                }
            }
            if ($shouldBeExcluded == true) {
                continue;
            }

            /* @var $f Flexed */
            file_put_Flexed($f);
            $history[] = $path;
        }

    }
    if (VERBOSE_FLEXIONS)
        fLog("\nSerializing hypotypose to " . count($history) . " file(s)" . cr(), true);
}

function file_put_Flexed(Flexed $f) {
    if (isset($f->source) && strlen($f->source) > Flexed::MIN_SOURCE_SIZE) {
        // Create the package folder if necessary
        if (!file_exists($f->packagePath)) {
            if (VERBOSE_FLEXIONS)
                fLog("-> creating package " . $f->package . cr(), true);
            mkdir($f->packagePath, 0755, true);
        }
        // Save the generated file
        file_put_contents($f->packagePath . $f->fileName, $f->source);
        if (VERBOSE_FLEXIONS)
            fLog("Writing : " . $f->packagePath . $f->fileName . cr(), true);
    } else {
        // We can return NULL sources to exclude a file from generation
    }

}

/**
 *
 * @param string $path
 * @return string
 */
function simplifyPath($path) {
    $path = realpath($path);
    $path = str_replace(FLEXIONS_ROOT_DIR, "", $path);
    return $path;
}


function injectVersionInPath($path) {
    $h = Hypotypose::Instance();
    $newPath = str_ireplace('{version.major}', $h->majorVersionPathSegmentString(), $path);
    return $newPath;
}
