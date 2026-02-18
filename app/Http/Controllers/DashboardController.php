<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $myCv = Cv::where('user_id', $user->id)->with('localizations')->first();

        $esReady = $myCv?->localizations->firstWhere('locale', 'es')?->title_name
            && $myCv?->localizations->firstWhere('locale', 'es')?->email;

        $enReady = $myCv?->localizations->firstWhere('locale', 'en')?->title_name
            && $myCv?->localizations->firstWhere('locale', 'en')?->email;

        return view('dashboard', [
            'myCv' => $myCv,
            'esReady' => (bool) $esReady,
            'enReady' => (bool) $enReady,
            'signatureReady' => ! empty($user->signature_file_path),
            'signatureSignedAt' => $user->signature_signed_at,
        ]);
    }
}
