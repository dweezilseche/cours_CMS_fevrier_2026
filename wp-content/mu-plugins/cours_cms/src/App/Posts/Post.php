<?php

namespace App\Posts;

use Timber\Post as TimberPost;
use Timber\Timber;

defined('ABSPATH') || exit;

class Post extends TimberPost
{
    public function getFormattedDate(string $format = 'd F Y'): string
    {
        return date_i18n($format, strtotime($this->post_date));
    }



}
