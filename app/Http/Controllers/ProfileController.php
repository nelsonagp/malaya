<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('perfil.show', [
            'user' => $request->user(),
        ]);
    }

    public function accounts(Request $request): View
    {
        return view('perfil.cuentas', [
            'user' => $request->user()->load('socialAccounts'),
            'providers' => ['google' => 'Google', 'facebook' => 'Facebook'],
        ]);
    }

    public function unlink(Request $request, string $provider): RedirectResponse
    {
        $request->user()->socialAccounts()->where('provider', $provider)->delete();

        return back()->with('status', 'Cuenta de '.ucfirst($provider).' desvinculada.');
    }
}
