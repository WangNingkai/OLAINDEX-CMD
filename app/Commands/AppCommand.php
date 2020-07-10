<?php
/**
 * This file is part of the wangningkai/olaindex-cmd.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Commands;


use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

class AppCommand extends Command
{
    /**
     * @var string
     */
    protected static $name = 'name';

    /**
     * @var string
     */
    protected static $description = 'description';

    /**
     * @usage
     * @arguments
     * @options
     * @param Input $input
     * @param Output $output
     * @return int|mixed|void
     */
    public function execute($input, $output)
    {

    }
}
