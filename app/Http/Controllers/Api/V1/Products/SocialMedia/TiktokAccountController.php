<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\TiktokAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\TiktokAccountResource;
use App\Services\Products\SocialMedia\TiktokAccountService;

class TiktokAccountController extends Controller
{
    public function __construct(readonly private TiktokAccountService $service)
    {
    }

    public function store(TiktokAccountRequest $request): TiktokAccountResource
    {
        $tiktokAccount = $this->service->storeOrUpdate($request->validated());
        return new TiktokAccountResource($tiktokAccount);
    }
}
