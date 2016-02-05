<?php

/**
 * Allow logging, with toggle support
 *
 * @package idno
 * @subpackage core
 */

namespace Idno\Core {
use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;

    class KnownLogger extends AbstractLogger implements LoggerInterface {

        public $loglevel_filter = 4;
        private $identifier;
        private $contexts = [];

        /**
         * Create a basic logger to log to the PHP log.
         *
         * @param type $loglevel_filter Log levels to show 0 - off, 1 - errors, 2 - errors & warnings, 3 - errors, warnings and info, 4 - 3 + debug
         * @param type $identifier Identify this site in the log (defaults to current domain)
         */
        public function __construct($loglevel_filter = 0, $identifier = null) {
            if (!$identifier)
                $identifier = \Idno\Core\Idno::site()->config->host;
            if (isset(\Idno\Core\Idno::site()->config->loglevel)) {
                $loglevel_filter = \Idno\Core\Idno::site()->config->loglevel;
            }

            $this->loglevel_filter = $loglevel_filter;
            $this->identifier = $identifier;
            $this->contexts = [];
        }

        /**
         * Write a message to the log.
         * @param string $level
         * @param string $message
         * @param array $context
         */
        public function log($level, $message, array $context = array()) {

            // See if this message isn't filtered out
            if ($level <= $this->loglevel_filter) {

                // Construct log message
                // Trace for debug (when filtering is set to debug, always add a trace)
                $trace = "";
                if ($this->loglevel_filter == 4) {
                    $backtrace = @debug_backtrace(false, 2);
                    if ($backtrace) {
                        // Never show this
                        $backtrace = $backtrace[0];

                        $trace = " [{$backtrace['file']}:{$backtrace['line']}]";
                    }
                }



                // Logging contexts

                if (!empty($context)) {
                    $context = ' [' . implode(';', $this->contexts) . ']';
                }

                error_log("Known ({$this->identifier}$context): $level - $message{$trace}");
            }
        }

    }

}
