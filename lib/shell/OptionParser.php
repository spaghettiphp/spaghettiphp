<?php
/**
 * This class is designed to ease the task of parsing the arguments that are provided
 * to scripts from the command line. It supports several advanced features including
 * support for both short and long options, optional and/or required parameter checking,
 * automatic callback execution, and pretty printing of usage messages.
 *
 * @author  Michael J. I. Jackson <mjijackson@gmail.com>
 */

class OptionParser
{

    /**
     * The current version of OptionParser.
     *
     * @var string
     */
    const VERSION = '0.4';

    /**#@+
     * Configuration constant.
     *
     * @var int
     */
    /**
     * Use to stop parsing arguments when a double hyphen (--) is found.
     */
    const CONF_DASHDASH = 1;

    /**
     * Use to ignore case when parsing flags.
     */
    const CONF_IGNORECASE = 2;

    /**
     * All configuration options.
     */
    const CONF_ALL = 3;
    /**#@-*/

    /**#@+
     * Parameter constant.
     *
     * @var int
     */
    /**
     * Used for a parameter that is not required.
     */
    const PARAM_NOTREQUIRED = 0;

    /**
     * Used for a parameter that is required.
     */
    const PARAM_REQUIRED = 1;

    /**
     * Used for a parameter that is optional.
     */
    const PARAM_OPTIONAL = 2;
    /**#@-*/

    /**
     * The configuration flag.
     *
     * @var int
     */
    protected $_config = 0;

    /**
     * The name of the program, as parsed from the arguments.
     *
     * @var string
     */
    protected $_programName;

    /**
     * Contains usage messages that should go at the beginning of the usage message.
     *
     * @var array
     */
    protected $_head = array();

    /**
     * Contains usage messages that should go at the end of the usage message.
     *
     * @var array
     */
    protected $_tail = array();

    /**
     * The parsed options. This is only available after {@link parse()} is called.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * The rules for this parser. Each rule is an array with three keys: description,
     * callback, and required.
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * A map of flags to the index in the {@link $_rules} array that contains the
     * corresponding rule for that flag.
     *
     * @var array
     */
    protected $_flags = array();

    /**
     * Constructor.
     *
     * @param   int     $config     An optional configuration flag
     */
    public function __construct($config=null)
    {
        if (is_int($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Alias for {@link setOption()}.
     *
     * @return  void
     */
    public function __set($flag, $value)
    {
        $this->setOption($flag, $value);
    }

    /**
     * Alias for {@link getOption()}.
     *
     * @return  mixed
     */
    public function __get($flag)
    {
        return $this->getOption($flag);
    }

    /**
     * Returns true if an option for the given flag is set.
     *
     * @return  bool
     */
    public function __isset($flag)
    {
        try {
            $this->checkFlag($flag);
        } catch (Exception $e) {
            return false;
        }

        $ruleIndex = $this->_flags[$flag];

        return isset($this->_options[$ruleIndex]);
    }

    /**
     * Unsets the given flag in this parser. Options for this flag
     * will no longer be available.
     *
     * @return  bool
     */
    public function __unset($flag)
    {
        unset($this->_flags[$flag]);
    }

    /**
     * Sets the configuration flag.
     *
     * @param   int
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * Gets the configuration flag.
     *
     * @return  int
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Gets the program name that was used to execute this script.
     *
     * @return  string
     */
    public function getProgramName()
    {
        return $this->_programName;
    }

    /**
     * Sets the $value of the option for the given $flag.
     *
     * @param   string  $flag       The name of the flag
     * @param   mixed   $value      The new value for the option
     * @return  void
     */
    public function setOption($flag, $value)
    {
        $this->checkFlag($flag);
        $ruleIndex = $this->_flags[$flag];
        $this->_options[$ruleIndex] = $value;
    }

    /**
     * Gets the value of the option for the given $flag.
     *
     * @param   string  $flag       The name of the flag
     * @return  mixed
     */
    public function getOption($flag)
    {
        $this->checkFlag($flag);
        $ruleIndex = $this->_flags[$flag];

        if (array_key_exists($ruleIndex, $this->_options)) {
            return $this->_options[$ruleIndex];
        } else {
            return false;
        }
    }

    /**
     * Add a usage message that will be displayed before the list of flags in the
     * output of {@link getUsage()}.
     *
     * @param   string  $message    The usage message
     * @return  void
     */
    public function addHead($message)
    {
        $this->_head[] = $message;
    }

    /**
     * Add a usage message that will be displayed after the list of flags in the
     * output of {@link getUsage()}.
     *
     * @param   string  $message    The usage message
     * @return  void
     */
    public function addTail($message)
    {
        $this->_tail[] = $message;
    }

    /**
     * Gets a usage message for this parser. The optional $maxWidth parameter can be
     * used to specify with width at which the message should wrap.
     *
     * @param   int
     * @return  string
     */
    public function getUsage($maxWidth=80)
    {
        $head = wordwrap(implode('', $this->_head), $maxWidth);
        $tail = wordwrap(implode('', $this->_tail), $maxWidth);

        # determine if there are any short flags
        $hasShortFlag = false;
        $allFlags = array_keys($this->_flags);
        foreach ($allFlags as $flag) {
            if (strlen($flag) == 1) {
                $hasShortFlag = true;
                break;
            }
        }

        $maxFlagsWidth = 0;
        $flagDescriptions = array();
        foreach ($this->_rules as $ruleIndex => $rule) {
            $flagList = array_keys($this->_flags, $ruleIndex);
            if (empty($flagList)) {
                continue;
            }
            usort($flagList, array($this, 'compareFlags'));
            $flags = array();
            $short = false;
            foreach ($flagList as $flag) {
                if (strlen($flag) == 1) {
                    $flags[] = "-$flag";
                    $short = true;
                } else {
                    $flags[] = "--$flag";
                }
            }
            $flags = implode(', ', $flags);
            if ($hasShortFlag && !$short) {
                $flags = "    $flags";
            }
            $maxFlagsWidth = max(strlen($flags), $maxFlagsWidth);
            $flagDescriptions[$flags] = $rule['description'];
        }

        $options = array();
        $format = " %-{$maxFlagsWidth}s  ";
        foreach ($flagDescriptions as $flags => $description) {
            $wrap = wordwrap($description, $maxWidth - ($maxFlagsWidth + 3));
            $lines = explode("\n", $wrap);
            $option = array(sprintf($format, $flags) . array_shift($lines));
            foreach ($lines as $line) {
                $option[] = str_repeat(' ', $maxFlagsWidth + 3) . $line;
            }
            $options[] = implode("\n", $option);
        }

        $usage = $head . "\n" . implode("\n", $options) . "\n" . $tail;

        return $usage;
    }

    /**
     * Adds a rule to this option parser. A rule consists of some flags that have
     * an optional description and/or callback function associated with them. The first
     * argument to this function should be the rule flags in a string seperated by a
     * pipe (|) character. The string may end in a colon (:) to indicate that the flag
     * accepts an optional parameter, or two colons (::) for a required parameter.
     * Remaining arguments may be a description, a callback function, both, or neither.
     *
     * If a description is given it will be used in the usage message for this parser
     * when describing this rule. If a callback function is given, it will be called when
     * any of the flags for this option are encountered in the arguments. Some examples
     * follow:
     *
     * <code>
     * $parser = new OptionParser;
     * $parser->addRule('a');
     * $parser->addRule('long-option');
     * $parser->addRule('b::'); // "b" with required parameter
     * $parser->addRule('q|quiet', "Don't output to the console"); // "q" or "quiet" with description
     * $parser->addRule('c', 'my_callback'); // my_callback will be called if the "c" flag is used
     * $parser->addRule('o|output:'); // "o" or "output" with optional parameter
     * </code>
     *
     * @param   string  $flags  The rule flags
     * @param   mixed   ...     The rule description or callback
     * @return  void
     */
    public function addRule()
    {
        $args = func_get_args();
        $rule = array(
            'description'   => '',
            'callback'      => null,
            'required'      => self::PARAM_NOTREQUIRED
        );

        $flags = array_shift($args);

        if ($this->_config & self::CONF_IGNORECASE) {
            $flags = strtolower($flags);
        }

        if (preg_match('/::?$/', $flags, $match)) {
            if (strlen($match[0]) == 2) {
                $rule['required'] = self::PARAM_REQUIRED;
            } else {
                $rule['required'] = self::PARAM_OPTIONAL;
            }
            $flags = rtrim($flags, ':');
        }

        # consume the remaining arguments
        while (count($args)) {
            $arg = array_pop($args);
            if (is_callable($arg)) {
                $rule['callback'] = $arg;
            } elseif (is_string($arg)) {
                $rule['description'] = $arg;
            }
        }

        $ruleIndex = count($this->_rules);

        foreach (explode('|', $flags) as $flag) {
            if ($flag) {
                $this->_flags[$flag] = $ruleIndex;
            }
        }

        $this->_rules[] = $rule;
    }

    /**
     * Gets the rule associated with the given flag.
     *
     * @return  array
     */
    public function getRule($flag)
    {
        try {
            $this->checkFlag($flag);
        } catch (Exception $e) {
            return null;
        }

        $ruleIndex = $this->_flags[$flag];

        return $this->_rules[$ruleIndex];
    }

    /**
     * Gets all rules of this parser.
     *
     * @return  array
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * Returns true if the given flag expects a parameter.
     *
     * @return  bool
     */
    public function expectsParameter($flag)
    {
        $rule = $this->getRule($flag);
        return $rule && $rule['required'] !== self::PARAM_NOTREQUIRED;
    }

    /**
     * Returns true if the given flag is required.
     *
     * @return  bool
     */
    public function isRequired($flag)
    {
        $rule = $this->getRule($flag);
        return $rule && $rule['required'] === self::PARAM_REQUIRED;
    }

    /**
     * Returns true if the given flag is optional.
     *
     * @return  bool
     */
    public function isOptional($flag)
    {
        $rule = $this->getRule($flag);
        return $rule && $rule['required'] === self::PARAM_OPTIONAL;
    }

    /**
     * Parses the given arguments according to this parser's rules. An exception will be
     * thrown if any rule is violated.
     *
     * Note: This function uses a reference to modify the given $argv array. If no argument
     * values are provided a copy of the global $argv array will be used.
     *
     * @param   array   $argv       The command line arguments
     * @return  void
     * @throws  Exception
     */
    public function parse(array &$argv=null)
    {
        $this->_options = array();

        if ($argv === null) {
            if (isset($_SERVER['argv'])) {
                $argv = $_SERVER['argv'];
            } else {
                $argv = array();
            }
        }

        $this->_programName = array_shift($argv);

        for ($i = 0; $i < count($argv); $i++) {
            if (preg_match('/^(--?)([a-z][a-z\-]*)(?:=(.+)?)?$/i', $argv[$i], $matches)) {
                if (isset($matches[3])) {
                    # put parameter back on stack of arguments
                    array_splice($argv, $i, 1, $matches[3]);
                    $paramGiven = true;
                } else {
                    # throw away the flag
                    array_splice($argv, $i, 1);
                    $paramGiven = false;
                }

                $flag = $matches[2];
                if ($this->_config & self::CONF_IGNORECASE) {
                    $flag = strtolower($flag);
                }

                if ($matches[1] == '--') {
                    # long flag
                    $this->parseOption($flag, $argv, $i, $paramGiven);
                } else {
                    # short flag
                    foreach (str_split($flag) as $shortFlag) {
                        $this->parseOption($shortFlag, $argv, $i, $paramGiven);
                    }
                }

                # decrement the index for the flag that was taken
                $i--;
            } elseif ($argv[$i] == '--') {
                if ($this->_config & self::CONF_DASHDASH) {
                    # stop processing arguments
                    break;
                }
            }
        }
    }

    /**
     * Extracts the option value for the given $flag from the arguments array.
     *
     * @param   string  $flag           The flag being parsed
     * @param   array   $argv           The argument values
     * @param   int     $i              The current index in the arguments array
     * @param   bool    $paramGiven     True if a parameter was given using the --flag=param syntax
     * @return  void
     */
    protected function parseOption($flag, array &$argv, $i, $paramGiven)
    {
        $this->checkFlag($flag);

        $ruleIndex = $this->_flags[$flag];
        $rule = $this->_rules[$ruleIndex];

        if ($rule['required'] == self::PARAM_REQUIRED) {
            if (isset($argv[$i]) && ($paramGiven || $this->isParam($argv[$i]))) {
                $slice = array_splice($argv, $i, 1);
                $param = $slice[0];
            } else {
                throw new Exception("Option \"$flag\" requires a parameter");
            }
        } elseif ($rule['required'] == self::PARAM_OPTIONAL) {
            if (isset($argv[$i]) && ($paramGiven || $this->isParam($argv[$i]))) {
                $slice = array_splice($argv, $i, 1);
                $param = $slice[0];
            } else {
                $param = true;
            }
        } else {
            $param = true;
        }

        if (is_callable($rule['callback'])) {
            call_user_func($rule['callback'], $param);
        }

        $this->_options[$ruleIndex] = $param;
    }

    /**
     * Returns true if the given string is considered a parameter.
     *
     * @param   string
     * @return  bool
     */
    protected function isParam($string)
    {
        if ($this->_config & self::CONF_DASHDASH && $string == '--') {
            return false;
        }

        return !$this->isFlag($string);
    }

    /**
     * Returns true if the given string is considered a flag.
     *
     * @param   string
     * @return  bool
     */
    protected function isFlag($string)
    {
        return (bool) preg_match('/^--?[a-z][a-z\-]*$/i', $string);
    }

    /**
     * Ensures the given flag is able to be recognized by this parser.
     *
     * @return  void
     * @throws  Exception
     */
    protected function checkFlag($flag)
    {
        if (!array_key_exists($flag, $this->_flags)) {
            throw new Exception("Option \"$flag\" is not recognized");
        }
    }

    /**
     * Used as a callback function for {@link usort()} when comparing two
     * flags. Flags are compared for length. If the two flags being compared are
     * of equal length, the flag that appeared first in the specification will
     * return first.
     *
     * @param   string
     * @param   string
     * @return  int
     */
    protected function compareFlags($a, $b)
    {
        $lena = strlen($a);
        $lenb = strlen($b);

        if ($lena == $lenb) {
            # returning -1 here will keep strings of the same length in the
            # same order they originally were in
            return -1;
        }

        return $lena > $lenb ? 1 : -1;
    }

    /**
     * Alias for {@link getUsage()}.
     *
     * @return  string
     */
    public function toString()
    {
        return $this->getUsage();
    }

    /**
     * Alias for {@link toString()}.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->toString();
    }

}