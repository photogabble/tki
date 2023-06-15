<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    public static function alreadyPublished(int $user_id, string $type): bool
    {
        return News::where('user_id', $user_id)
            ->where('news_type', $type)
            ->count() > 0;
    }
}
