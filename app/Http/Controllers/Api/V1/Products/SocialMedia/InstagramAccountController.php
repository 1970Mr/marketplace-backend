<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\InstagramAccountRequest;
use App\Http\Resources\V1\Products\SocialMedia\InstagramAccountResource;
use App\Services\Products\SocialMedia\InstagramAccountService;

class InstagramAccountController extends Controller
{
    public function __construct(readonly private InstagramAccountService $service)
    {
    }

    public function store(InstagramAccountRequest $request): InstagramAccountResource
    {
        $instagramAccount = $this->service->storeOrUpdate($request->validated());
        return new InstagramAccountResource($instagramAccount);
    }
}
