<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker;

use Psy\Exception\BreakException;
use Psy\Exception\ThrowUpException;
use Psy\Shell;
use Throwable;

class ExecutionClosure extends \Psy\ExecutionClosure
{
    public function __construct(Shell $__psysh__)
    {
        $this->setClosure($__psysh__, function () use ($__psysh__) {
            // Restore execution scope variables
            \extract($__psysh__->getScopeVariables(false));

            try {
                try {
                    // Buffer stdout; we'll need it later
                    \ob_start([$__psysh__, 'writeStdout'], 1);

                    // Convert all errors to exceptions
                    \set_error_handler([$__psysh__, 'handleError']);

                    // Evaluate the current code buffer
                    $_ = eval($__psysh__->onExecute($__psysh__->flushCode() ?: self::NOOP_INPUT));
                } catch (Throwable $_e) {
                    // Clean up on our way out.
                    if (\ob_get_level() > 0) {
                        \ob_end_clean();
                    }

                    throw $_e;
                } finally {
                    // Won't be needing this anymore
                    \restore_error_handler();
                }

                // Flush stdout (write to shell output, plus save to magic variable)
                \ob_end_flush();

                // Save execution scope variables for next time
                $__psysh__->setScopeVariables(\get_defined_vars());

                $__psysh__->writeReturnValue($_);
            } catch (BreakException $_e) {
                $__psysh__->writeException($_e);
            } catch (ThrowUpException $_e) {
                $__psysh__->writeException($_e);

                throw $_e;
            } catch (Throwable $_e) {
                $__psysh__->writeException($_e);
            }
        });
    }
}
