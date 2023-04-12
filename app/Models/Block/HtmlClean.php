<?php
namespace App\Models\Block;

use App\Models\BaseBlock;

class HtmlClean extends BaseBlock
{
    function __construct() 
    {
        parent::__construct(self::class);
    }
}
