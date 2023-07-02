<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    /* public function __invoke(Request $request)
    {
        //
    } */

    /**
     * Upload the user's avatar.
     */
    public function upload(Request $request): RedirectResponse
    {
        /* $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]); */

        /* $file = $request->file('file_input');
        $name = $file->getClientOriginalName();

        $upload = Storage::putFile("public/avatars", $file); */

        $file = $request->file('file_input');

        $name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        $upload = Storage::putFileAs("public/avatars", $file, $name);

        error_log($upload);

        $request->user()->update([
            'avatar_url' => $upload,
        ]);

        return back();
    }
}
