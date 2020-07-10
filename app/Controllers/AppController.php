<?php
/**
 * This file is part of the wangningkai/olaindex-cmd.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controllers;


use Inhere\Console\Controller;

class AppController extends Controller
{
    /**
     * @var string
     */
    protected static $name = 'app';

    /**
     * @var string
     */
    protected static $description = 'default command controller. there are some command usage examples';

    /**
     * this is a command's description message, <cyan>color text</cyan>
     * the second line text
     * @usage {command} [arg ...] [--opt ...]
     * @arguments
     *  arg1        argument description 1
     *              the second line
     *  a2,arg2     argument description 2
     *              the second line
     * @options
     *  -s, --long  option description 1
     *  --opt       option description 2
     * @example example text one
     *  the second line example
     */
    public function indexCommand()
    {
        $this->write('hello, welcome!! this is ' . __METHOD__);
    }
}
