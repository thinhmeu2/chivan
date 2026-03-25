<?php

namespace App\View\Components;

trait TraitSoicau
{
    public function getTextMiss(): string
    {
        return "<span class=text-666>Trượt</span>";
    }
    public function getTextWaiting(): string
    {
        return '...';
    }
}
