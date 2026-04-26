<?php

namespace Modules\UserDietSection\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\UserDietSection\Http\Requests\StoreUserHealthProfileRequest;
use Modules\UserDietSection\Services\UserDietSectionService;
use Modules\UserDietSection\Transformers\UserHealthProfileResource;

class UserDietSectionController extends Controller
{
    public function __construct(private UserDietSectionService $service)
    {
    }
    public function store(StoreUserHealthProfileRequest $request)
    {
        $healthProfile = $this->service->store($request->validated());
        if ($healthProfile)
            return $this->success(new UserHealthProfileResource ($healthProfile), 'health profile created successful');
        else
            return $this->error('Operation failed');


    }
}
