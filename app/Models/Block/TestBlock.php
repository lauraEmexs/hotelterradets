<?php
namespace App\Models\Block;

use App\Models\BaseBlock;

class TestBlock extends BaseBlock
{
    function __construct() 
    {
        parent::__construct(self::class);
    }
}
