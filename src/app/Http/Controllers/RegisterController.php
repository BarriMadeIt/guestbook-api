<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterValidation;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Handle the incoming register request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function __invoke(RegisterValidation $request)
    {
        $user = User::create($request->validated());

        return [
            'token' => $user->createToken('Login')->plainTextToken,
        ];
    }
}
