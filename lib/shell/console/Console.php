<?php
/*
(c) 2006 Jan Kneschke <jan@kneschke.de>

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

@ob_end_clean();
error_reporting(E_ALL);
set_time_limit(0);

require_once CONSOLE_ROOT . "Shell.php";
require_once CONSOLE_ROOT . "Shell/Extensions/Autoload.php";
require_once CONSOLE_ROOT . "Shell/Extensions/AutoloadDebug.php";
require_once CONSOLE_ROOT . "Shell/Extensions/Colour.php";
require_once CONSOLE_ROOT . "Shell/Extensions/ExecutionTime.php";
require_once CONSOLE_ROOT . "Shell/Extensions/InlineHelp.php";
require_once CONSOLE_ROOT . "Shell/Extensions/VerbosePrint.php";
require_once CONSOLE_ROOT . "Shell/Extensions/LoadScript.php";
    
function __shell_default_error_handler($errno, $errstr, $errfile, $errline, $errctx) {
    if ($errno == 2048) return;
  
    throw new Exception(sprintf("%s:%d\r\n%s", $errfile, $errline, $errstr));
}

set_error_handler("__shell_default_error_handler");

$__shell = new PHP_Shell();
$__shell_exts = PHP_Shell_Extensions::getInstance();
$__shell_exts->registerExtensions(array(
    "options"        => PHP_Shell_Options::getInstance(),
    "autoload"       => new PHP_Shell_Extensions_Autoload(),
    "autoload_debug" => new PHP_Shell_Extensions_AutoloadDebug(),
    "colour"         => new PHP_Shell_Extensions_Colour(),
    "exectime"       => new PHP_Shell_Extensions_ExecutionTime(),
    "inlinehelp"     => new PHP_Shell_Extensions_InlineHelp(),
    "verboseprint"   => new PHP_Shell_Extensions_VerbosePrint(),
    "loadscript"     => new PHP_Shell_Extensions_LoadScript(),
));

$f = <<<EOF
>> use '?' to open the inline help 

EOF;

printf($f);
unset($f);

print $__shell_exts->colour->getColour("default");
while($__shell->input()) {
    if ($__shell_exts->autoload->isAutoloadEnabled() && !function_exists('__autoload')) {
        function __autoload($classname) {
            global $__shell_exts;

            if ($__shell_exts->autoload_debug->isAutoloadDebug()) {
                print str_repeat(".", $__shell_exts->autoload_debug->incAutoloadDepth())." -> autoloading $classname".PHP_EOL;
            }
            include_once str_replace('_', '/', $classname).'.php';
            if ($__shell_exts->autoload_debug->isAutoloadDebug()) {
                print str_repeat(".", $__shell_exts->autoload_debug->decAutoloadDepth())." <- autoloading $classname".PHP_EOL;
            }
        }
    }

    try {
        $__shell_exts->exectime->startParseTime();
        if ($__shell->parse() == 0) {

            $__shell_exts->exectime->startExecTime();

            $__shell_retval = eval($__shell->getCode()); 
            if (isset($__shell_retval)) {
                print $__shell_exts->colour->getColour("value");

                if (function_exists("__shell_print_var")) {
                    __shell_print_var($__shell_retval, $__shell_exts->verboseprint->isVerbose());
                } else {
                    var_export($__shell_retval);
                }
            }
            unset($__shell_retval);
            $__shell->resetCode();
        }
    } catch(Exception $__shell_exception) {
        print $__shell_exts->colour->getColour("exception");
        printf('%s (code: %d) got thrown'.PHP_EOL, get_class($__shell_exception), $__shell_exception->getCode());
        print $__shell_exception;
        
        $__shell->resetCode();

        unset($__shell_exception);
    }
    print $__shell_exts->colour->getColour("default");
    $__shell_exts->exectime->stopTime();
    if ($__shell_exts->exectime->isShow()) {
        printf(" (parse: %.4fs, exec: %.4fs)", 
            $__shell_exts->exectime->getParseTime(),
            $__shell_exts->exectime->getExecTime()
        );
    }
}

print $__shell_exts->colour->getColour("reset");