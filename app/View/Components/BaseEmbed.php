<?php

namespace App\View\Components;

use App\Helpers\DateHelper;
use App\Services\FeServices\KeyService;
use Illuminate\View\Component;

abstract class BaseEmbed extends Component
{
    final public function __construct()
    {

    }
    abstract protected function getKey(): string;
    abstract protected function logicGetData(): array;

    /**
     * Get the view / contents that represent the component.
     */
    final public function render(): string
    {
        $keyService = resolve(KeyService::class);
        $viewName = $this->getKey();
        $today = DateHelper::today();
        $html = $keyService->getHtmlFromDb($viewName, $today);
        if (! $html){
            $html = $this->view("components.$viewName", $this->logicGetData());
            $keyService->saveHtml($viewName, $today, $html);
        }
        return $html;
    }
}
