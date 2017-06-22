<?php
    use \Process\Mp;

    require('init.inc');
    if (isset($argv[1])) {
        if (isset($argv[2])) {
            $lockFile = __FILE__ . '.thread' . $argv[1] . '.' . $argv[2] . '.lock';
        } else {
            $lockFile = __FILE__ . '.thread' . $argv[1] . '.lock';
        }
    } else {
        $lockFile = __FILE__ . '.lock';
    }
    if(is_file($lockFile))
    {//如果此程序已运行，则退出
        if(false === @pcntl_getpriority(file_get_contents($lockFile))) {
            unlink($lockFile);
            file_put_contents($lockFile, getmypid());
        } else {
            exit('Program Is Runing...');
        }
    } else {
        file_put_contents($lockFile, getmypid());
    }
    ini_set('memory_limit',-1);

    $process = new \Process\Mp\Queue($globalConfig);
    $process->run();

    unlink($lockFile);
?>
