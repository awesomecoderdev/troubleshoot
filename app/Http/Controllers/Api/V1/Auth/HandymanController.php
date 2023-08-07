<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\RegisteredHandyman;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHandymanRequest;
use App\Models\Handyman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HandymanController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(StoreHandymanRequest $request)
    {
        return response()->json([
            "status" => "Hello world",
            "request" => $request->all(),
            "csrf" => $request->session()->token()
        ]);
        // $request->validate([
        //     'name' => ['required', 'string', 'max:255'],
        //     'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
        //     'password' => ['required', 'confirmed', Rules\Password::defaults()],
        // ]);

        $handyman = Handyman::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new RegisteredHandyman($handyman));

        Auth::login($handyman);

        // return redirect(RouteServiceProvider::HOME);
    }
}
