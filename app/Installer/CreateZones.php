<?php

namespace App\Installer;

// Second half of 60.php
use Illuminate\Console\OutputStyle;

class CreateZones implements InstallStep{
    public function execute(OutputStyle $output, InstallConfig $config): int
    {

        // Insert zones - Unchartered, fed, free trade, war & Fed space

        // Finding random sectors where port=none and getting their sector ids in one sql query

        // TODO: Insert special ports

        // Finding random sectors where port=none and getting their sector ids in one sql query
        // For Ore Ports

        // TODO: Insert ore ports

        // Finding random sectors where port=none and getting their sector ids in one sql query
        // For Organic Ports

        // TODO: Insert organics ports

        // Finding random sectors where port=none and getting their sector ids in one sql query
        // For Goods Ports

        // TODO: Insert goods ports

        // Finding random sectors where port=none and getting their sector ids in one sql query
        // For Energy Ports

        // TODO: Insert energy ports

        return 0;
    }
}