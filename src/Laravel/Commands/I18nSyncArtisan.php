<?php

namespace Ztphp\I18n\Laravel\Commands;

use Illuminate\Console\Command;
use Ztphp\I18n\I18nSyncInterface;

class I18nSyncArtisan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zt-i18n:sync {timeout=0} {--O|once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '执行多语言从阿波罗同步';


    public function handle(I18nSyncInterface $i18nSync)
    {
        $timeout = $this->input->getArgument('timeout');
        $once = $this->input->getOption('once');

        if ($once) {
            $i18nSync->syncOnce();
        }else{
            $i18nSync->syncAndListen($timeout);
        }
    }
}