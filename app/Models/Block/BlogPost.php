<?php
namespace App\Models\Block;

use App\Models\BaseBlock;
use App\Models\User_general_model;

class BlogPost extends BaseBlock
{
    function __construct()
    {
        parent::__construct(self::class);
    }

    public function prepare_view($block,$controllerData)
    {
        $data = parent::prepare_view($block,$controllerData);
        return $data;
    }
}
