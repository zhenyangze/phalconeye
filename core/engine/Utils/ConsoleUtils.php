<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+

  +------------------------------------------------------------------------+
  | PhalconEye CMS                                                         |
  +------------------------------------------------------------------------+
  | Copyright (c) 2013-2016 PhalconEye Team (http://phalconeye.com/)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconeye.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Ivan Vorontsov <lantian.ivan@gmail.com>                 |
  +------------------------------------------------------------------------+
*/

namespace Engine\Utils;

use Phalcon\Logger;

/**
 * Console utils.
 *
 * @category  PhalconEye
 * @package   Engine\Utils
 * @author    Ivan Vorontsov <lantian.ivan@gmail.com>
 * @copyright 2013-2016 PhalconEye Team
 * @license   New BSD License
 * @link      http://phalconeye.com/
 */
final class ConsoleUtils
{
    const COMMENT_START_POSITION = 42;

    const FG_BLACK = 1;

    const FG_DARK_GRAY = 2;

    const FG_BLUE = 3;

    const FG_LIGHT_BLUE = 4;

    const FG_GREEN = 5;

    const FG_LIGHT_GREEN = 6;

    const FG_CYAN = 7;

    const FG_LIGHT_CYAN = 8;

    const FG_RED = 9;

    const FG_LIGHT_RED = 10;

    const FG_PURPLE = 11;

    const FG_LIGHT_PURPLE = 12;

    const FG_BROWN = 13;

    const FG_YELLOW = 14;

    const FG_LIGHT_GRAY = 15;

    const FG_WHITE = 16;

    const BG_BLACK = 1;

    const BG_RED = 2;

    const BG_GREEN = 3;

    const BG_YELLOW = 4;

    const BG_BLUE = 5;

    const BG_MAGENTA = 6;

    const BG_CYAN = 7;

    const BG_LIGHT_GRAY = 8;

    const AT_NORMAL = 1;

    const AT_BOLD = 2;

    const AT_ITALIC = 3;

    const AT_UNDERLINE = 4;

    const AT_BLINK = 5;

    const AT_OUTLINE = 6;

    const AT_REVERSE = 7;

    const AT_NONDISP = 8;

    const AT_STRIKE = 9;

    /**
     * @var array Map of supported foreground colors.
     */
    private static $_fg = [
        self::FG_BLACK => '0;30',
        self::FG_DARK_GRAY => '1;30',
        self::FG_RED => '0;31',
        self::FG_LIGHT_RED => '1;31',
        self::FG_GREEN => '0;32',
        self::FG_LIGHT_GREEN => '1;32',
        self::FG_BROWN => '0;33',
        self::FG_YELLOW => '1;33',
        self::FG_BLUE => '0;34',
        self::FG_LIGHT_BLUE => '1;34',
        self::FG_PURPLE => '0;35',
        self::FG_LIGHT_PURPLE => '1;35',
        self::FG_CYAN => '0;36',
        self::FG_LIGHT_CYAN => '1;36',
        self::FG_LIGHT_GRAY => '0;37',
        self::FG_WHITE => '1;37',
    ];

    /**
     * @var array Map of supported background colors.
     */
    private static $_bg = [
        self::BG_BLACK => '40',
        self::BG_RED => '41',
        self::BG_GREEN => '42',
        self::BG_YELLOW => '43',
        self::BG_BLUE => '44',
        self::BG_MAGENTA => '45',
        self::BG_CYAN => '46',
        self::BG_LIGHT_GRAY => '47',
    ];

    /**
     * @var array Map of supported attributes.
     */
    private static $_at = [
        self::AT_NORMAL => '0',
        self::AT_BOLD => '1',
        self::AT_ITALIC => '3',
        self::AT_UNDERLINE => '4',
        self::AT_BLINK => '5',
        self::AT_OUTLINE => '6',
        self::AT_REVERSE => '7',
        self::AT_NONDISP => '8',
        self::AT_STRIKE => '9'
    ];

    /**
     * Supported terminals.
     *
     * @var string
     */
    private static $_supportedShells = [
        'xterm' => true,
        'xterm-256color' => true,
        'xterm-color' => true
    ];

    /**
     * Log by logger type.
     *
     * @param int    $type Message type.
     * @param string $msg  Message.
     *
     * @return string
     */
    public static function log($type, $msg)
    {
        switch ($type) {
            case Logger::SPECIAL:
            case Logger::CUSTOM:
            case Logger::DEBUG:
                return static::debug($msg);
                break;
            case Logger::INFO:
                return static::info($msg);
                break;
            case Logger::NOTICE:
            case Logger::WARNING:
                return static::warn($msg);
                break;
            case Logger::ERROR:
            case Logger::ALERT:
            case Logger::CRITICAL:
            case Logger::EMERGENCE:
            case Logger::EMERGENCY:
                return static::error($msg);
                break;
        }

        return '';
    }

    /**
     * Color style for error messages.
     *
     * @param string $msg Message to print.
     *
     * @return string
     */
    public static function errorLine($msg)
    {
        $msg = 'Error: ' . $msg;
        $space = strlen($msg) + 4;
        $out = self::colorize(
            str_pad(' ', $space), ConsoleUtils::FG_WHITE, ConsoleUtils::AT_BOLD, ConsoleUtils::BG_RED
        );
        $out .= PHP_EOL;

        $out .= self::colorize(
            '  ' . $msg . '  ', ConsoleUtils::FG_WHITE, ConsoleUtils::AT_BOLD, ConsoleUtils::BG_RED
        );
        $out .= PHP_EOL;

        $out .= self::colorize(
            str_pad(' ', $space), ConsoleUtils::FG_WHITE, ConsoleUtils::AT_BOLD, ConsoleUtils::BG_RED
        );
        $out .= PHP_EOL . PHP_EOL;

        return $out;
    }

    /**
     * Color style for error messages.
     *
     * @param string $msg Message to print.
     *
     * @return string
     */
    public static function error($msg)
    {
        return self::colorize($msg, ConsoleUtils::FG_RED, ConsoleUtils::AT_BOLD);
    }

    /**
     * Colorizes the string using provided colors.
     *
     * @param string       $string String to colorize.
     * @param null|integer $fg     Foreground.
     * @param null|integer $at     Attribute.
     * @param null|integer $bg     Background.
     *
     * @return string
     */
    public static function colorize($string, $fg = null, $at = null, $bg = null)
    {
        // Shell not supported, exit early
        if (!self::isSupportedShell()) {
            return $string;
        }

        $colored = '';

        // Check if given foreground color is supported
        if (isset(self::$_fg[$fg])) {
            $colored .= "\033[" . self::$_fg[$fg] . "m";
        }

        // Check if given background color is supported
        if (isset(self::$_bg[$bg])) {
            $colored .= "\033[" . self::$_bg[$bg] . "m";
        }

        // Check if given attribute is supported
        if (isset(self::$_at[$at])) {
            $colored .= "\033[" . self::$_at[$at] . "m";
        }

        // Add string and end coloring
        $colored .= $string . "\033[0m";

        return $colored;
    }

    /**
     * Identify if console supports colors.
     *
     * @return boolean
     */
    public static function isSupportedShell()
    {
        if (isset($_ENV['TERM'])) {
            if (isset(self::$_supportedShells[$_ENV['TERM']])) {
                return true;
            }
        } elseif (isset($_SERVER['TERM'])) {
            if (isset(self::$_supportedShells[$_SERVER['TERM']])) {
                return true;
            }
        } elseif (isset($_SERVER['ConEmuANSI']) && $_SERVER['ConEmuANSI'] == 'ON') {
            return true;
        }

        return false;
    }

    /**
     * Color style for success messages.
     *
     * @param string $msg Message to print.
     *
     * @return string
     */
    public static function successLine($msg)
    {
        $msg = 'Success: ' . $msg;
        $space = strlen($msg) + 4;
        $out = self::colorize(
            str_pad(' ', $space), ConsoleUtils::FG_WHITE, ConsoleUtils::AT_BOLD, ConsoleUtils::BG_GREEN
        );
        $out .= PHP_EOL;

        $out .= self::colorize(
            '  ' . $msg . '  ', ConsoleUtils::FG_WHITE, ConsoleUtils::AT_BOLD, ConsoleUtils::BG_GREEN
        );
        $out .= PHP_EOL;

        $out .= self::colorize(
            str_pad(' ', $space), ConsoleUtils::FG_WHITE, ConsoleUtils::AT_BOLD, ConsoleUtils::BG_GREEN
        );
        $out .= PHP_EOL;

        return $out;
    }

    /**
     * Get warning message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function warn($msg)
    {
        return self::colorize($msg, ConsoleUtils::FG_BROWN, ConsoleUtils::AT_BOLD);
    }

    /**
     * Get warning line message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function warnLine($msg)
    {
        return self::warn($msg) . PHP_EOL . PHP_EOL;
    }

    /**
     * Get debug message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function debug($msg)
    {
        return self::colorize($msg, ConsoleUtils::FG_LIGHT_CYAN, ConsoleUtils::AT_BOLD);
    }

    /**
     * Get debug line message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function debugLine($msg)
    {
        return self::debug($msg) . PHP_EOL . PHP_EOL;
    }

    /**
     * Get info message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function info($msg)
    {
        return self::colorize($msg, ConsoleUtils::FG_GREEN, ConsoleUtils::AT_BOLD);
    }

    /**
     * Get info line message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function infoLine($msg)
    {
        return self::info($msg) . PHP_EOL . PHP_EOL;
    }

    /**
     * Get info line message.
     *
     * @param string $msg             Message text.
     * @param bool   $lineBefore      Print empty line before msg.
     * @param int    $afterLinesCount Print empty lines after msg.
     * @param int    $color           Info color.
     *
     * @return string
     */
    public static function infoSpecial($msg, $lineBefore = true, $afterLinesCount = 2, $color = ConsoleUtils::FG_GREEN)
    {
        $out = '';
        if ($lineBefore) {
            $out .= PHP_EOL;
        }

        $out .= self::colorize($msg, $color, ConsoleUtils::AT_BOLD);

        if ($afterLinesCount) {
            for ($i = 0; $i < $afterLinesCount; $i++) {
                $out .= PHP_EOL;
            }
        }

        return $out;
    }

    /**
     * Get heading line message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function head($msg)
    {
        return self::colorize($msg, ConsoleUtils::FG_BROWN) . PHP_EOL;
    }

    /**
     * Get command line message.
     *
     * @param string $cmd           Message text.
     * @param string $comment       Comment text.
     * @param int    $commentColor  Comment text color.
     * @param int    $cmdColor      Comment text color.
     * @param int    $startPosition Start position of a comment.
     *
     * @return string
     */
    public static function command(
        $cmd,
        $comment = '',
        $commentColor = ConsoleUtils::FG_BROWN,
        $cmdColor = ConsoleUtils::FG_GREEN,
        $startPosition = self::COMMENT_START_POSITION
    )
    {
        $messageLength = strlen($cmd) + 3;

        return self::colorize('  ' . $cmd, $cmdColor) .
        self::tab($startPosition, $messageLength) .
        self::colorize($comment, $commentColor) .
        PHP_EOL;
    }

    /**
     * Get just text line message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function text($msg)
    {
        return self::colorize('  ' . $msg);
    }

    /**
     * Get just text line message.
     *
     * @param string $msg Message text.
     *
     * @return string
     */
    public static function textLine($msg)
    {
        return self::text($msg) . PHP_EOL . PHP_EOL;
    }

    /**
     * Make tab space.
     *
     * @param int $start  Starting from.
     * @param int $length With length.
     *
     * @return string
     */
    public static function tab($start, $length)
    {
        if (!self::isSupportedShell()) {
            return str_repeat(' ', $length);
        }

        return "\033[{$length}D\033[{$start}C";
    }
}
