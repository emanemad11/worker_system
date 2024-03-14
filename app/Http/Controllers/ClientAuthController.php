<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Worker;
use Validator;

class ClientAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:client', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = auth()->guard('client')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|between:2,100',
    //         'email' => 'required|string|email|max:100|unique:workers',
    //         'password' => 'required|string|min:6',
    //         'phone' => 'required|string|min:6',
    //         'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // الحد الأقصى لحجم الصورة بالكيلوبايت
    //         'location' => 'required|string|min:6',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors()->toJson(), 400);
    //     }
    //     // if ($request->hasFile('photo')) {
    //     //     $photo = $request->file('photo');
    //     //     $photoName = time() . '_' . $photo->getClientOriginalName();
    //     //     $photo->move(public_path('photos'), $photoName);
    //     // } else {
    //     //     $photoName = null; // أو أي قيمة افتراضية أخرى تناسب التطبيق
    //     // }
    //     $photoPath = null;
    //     if ($request->hasFile('photo')) {
    //         $photoPath = $request->file('photo')->store('worker');
    //     }
    //     $worker = Worker::create(array_merge(
    //         $validator,
    //         [
    //             'password' => bcrypt($request->password),
    //             'photo' => $photoPath,
    //         ]
    //     ));
    //     return response()->json([
    //         'message' => 'User successfully registered',
    //         'user' => $worker
    //     ], 201);
    // }
    public function register(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100|unique:clients',
        'password' => 'required|string|min:6', // Add password confirmation
        'photo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048', // Allow null photo

    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }

    // Store photo if provided
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        $photoName = time() . '_' . $photo->getClientOriginalName();
        $photo->storeAs('worker', $photoName);
        $photoPath = $photoName;
    }

    // Create worker record
    $client = Client::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'photo' => $photoPath,
    ]);

    // Return successful response
    return response()->json([
        'message' => 'client successfully registered',
        'client' => $client,
    ], 201);
}


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('client')->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->guard('client')->user());
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->guard('client')->user()
        ]);
    }
}
