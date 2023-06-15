<?php

namespace Tki\Installer;

// 70.php
use Tki\Models\News;
use Illuminate\Console\OutputStyle;

class CreateNews extends Step implements InstallStep{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {
        // Create big bang news event

        $news = new News();
        $news->headline = 'Big Bang!';
        $news->body = __('create_universe.l_cu_bigbang');
        $news->type = 'col25';

        $news->save();

        return 0;
    }
}