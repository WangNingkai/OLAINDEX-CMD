<?php
/**
 * This file is part of the wangningkai/olaindex-cmd.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/* @var \Inhere\Console\Component\PharCompiler $compiler */
$compiler
    ->stripComments(true)
    ->setShebang(true)
    ->addExclude([
        'demo',
        'test',
        'tmp',
        'runtime'
    ])
    ->addFile([
        'LICENSE',
        'composer.json',
        'README.md',
        'config/bootstrap.php',
    ])
    ->setCliIndex('bin/app');

// Console 下的 Command Controller 命令类不去除注释，注释上是命令帮助信息
$compiler->setStripFilter(static function ($file) {
    /** @var \SplFileInfo $file */
    $name = $file->getFilename();

    return false === strpos($name, 'Command.php') && false === strpos($name, 'Controller.php');
});
