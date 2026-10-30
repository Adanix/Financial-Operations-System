<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $settings = BusinessSetting::current();

        return view('landing', [
            'settings' => $settings,
            'contactEmail' => 'nixresponding@gmail.com',
            'currencySymbol' => $settings->currency_symbol ?? '$',
        ]);
    }
}
