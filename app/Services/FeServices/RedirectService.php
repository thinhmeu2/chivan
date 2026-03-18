<?php
namespace App\Services\FeServices;

use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;

class RedirectService extends BaseService
{
    public array $selectList = ['from', 'to'];
    public array $selectDetail = ['from', 'to'];
    public function __construct()
    {
        parent::__construct(resolve(Redirect::class));
    }
    public function redirectFrom($from): ?RedirectResponse
    {
        return null;
        return \redirect()->to('')->setStatusCode(301);
    }
}
