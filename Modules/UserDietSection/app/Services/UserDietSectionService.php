<?php

namespace Modules\UserDietSection\Services;

use Exception;
use Modules\UserDietSection\Models\UserHealthProfile;

class UserDietSectionService
{
    public function store($data)
    {
        if (UserHealthProfile::where('user_id',auth()->id())->exists()) {
            return throw new Exception('User already exsits');
        }
        $data['user_id'] = auth()->id();
        return UserHealthProfile::create($data);
    }
}
