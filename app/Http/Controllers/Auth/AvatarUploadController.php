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
use Illuminate\Support\Facades\Http;

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

        $name = $file->getClientOriginalName(); // will include extension
        // $extension = $file->getClientOriginalExtension();

        $upload = Storage::putFileAs("public/avatars", $file, $name);

        // Perform malware check

        $analysisId = $this->uploadVirusTotal($name);

        if ($analysisId == null) {
            // Fail
            error_log("Virus total file up has failed");
        }

        $analysisResponse;

        $isAnalysisCompleted = false;

        while(! $isAnalysisCompleted) {

            $analysisResponse = $this-> getAnalysis($analysisId);

            if (strcmp($analysisResponse['data']['attributes']['status'], "completed") == 0) {
                $isAnalysisCompleted = true;
            }

            error_log($analysisResponse['data']['attributes']['status']);

            // if still queued, keep polling every 10 seconds
            sleep(10);
        }

        error_log($analysisResponse);

        // If more than 1 engine detects malicious, remove the file
        if ($analysisResponse['data']['attributes']['stats']['malicious'] > 1) {
            Storage::delete("public/avatars/" . $name);
        }

        error_log($upload);

        $request->user()->update([
            'avatar_url' => '/storage/avatars/' . $name,
        ]);

        return back();
    }

    public function get(Request $request)
    {
        return $request->user()->avatar_url;
    }

    public function uploadVirusTotal(String $filename)
    {
        $key = env('VT_KEY');

        /* $response = Http::withHeaders([
            'accept' => 'application/json',
            'x-apikey' => $key
        ])->attach(
            'file', base64_encode(Storage::get("public/avatars/" . $filename)), $filename
        )->post('https://www.virustotal.com/api/v3/files'); */

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'x-apikey' => $key
        ])->attach(
            'file', file_get_contents('../storage/app/public/avatars/' . $filename), $filename
        )->post('https://www.virustotal.com/api/v3/files');

        error_log($response);

        // Get analysis id from request and post to /analyses/<analysis ID>
        $analysisId = $response['data']['id'];

        return $analysisId;
    }

    public function getAnalysis(String $analysisId) {

        $key = env('VT_KEY');

        $response = Http::withHeaders([
            'x-apikey' => $key
        ])->get(sprintf('https://www.virustotal.com/api/v3/analyses/%s', $analysisId));

        return $response;
    }
}
