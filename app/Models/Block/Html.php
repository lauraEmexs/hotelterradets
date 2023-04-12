<?php
namespace App\Models\Block;

use App\Models\BaseBlock;

class Html extends BaseBlock
{
    function __construct() 
    {
        parent::__construct(self::class);
    }
}
