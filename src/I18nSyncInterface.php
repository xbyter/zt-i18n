<?php

namespace ZtI18n;

interface I18nSyncInterface
{
    /**
     * 同步并监听阿波罗i18n改变
     * @param int $listenTimeout 监听多久，0为一直监听，定时任务的话则是60的倍数
     * @return void
     */
    public function syncAndListen(int $listenTimeout = 0);

    /**
     * 同步一次阿波罗i18n
     * @return mixed
     */
    public function syncOnce();
}