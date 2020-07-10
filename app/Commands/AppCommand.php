<?php
/**
 * This file is part of the wangningkai/olaindex-cmd.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Commands;


use App\Service\Log;
use Inhere\Console\Command;

class AppCommand extends Command
{
    /**
     * @var string
     */
    protected static $name = 'test';

    /**
     * @var string
     */
    protected static $description = 'this is a test independent command';

    /**
     * @usage usage message
     * @arguments
     *  arg     some message ...
     *
     * @options
     *  -o, --opt     some message ...
     *
     * @param \Inhere\Console\IO\Input $input
     * @param \Inhere\Console\IO\Output $output
     * @return void
     */
    public function execute($input, $output)
    {
        $output->write('hello, this in ' . __METHOD__);
    }
}
