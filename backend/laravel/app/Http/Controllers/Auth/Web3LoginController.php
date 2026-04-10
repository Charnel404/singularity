<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class Web3LoginController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $token = PersonalAccessToken::findToken($request->string('token'));

        if (! $token) {
            return redirect()
                ->route('login')
                ->with('error', 'Invalid authentication token.');
        }

        $user = $token->tokenable;

        if (! $user || ! ($user instanceof User)) {
            return redirect()
                ->route('login')
                ->with('error', 'Authentication failed.');
        }

        DB::transaction(function () use ($user, $token) {
            $token->delete();

            if ($user->current_team_id) {
                $team = $user->teams()->first();
            } else {
                $team = $user->personalTeam();
            }

            if ($team) {
                $user->forceFill([
                    'current_team_id' => $team->id,
                ])->save();
            }

            request()->session()->regenerate();

            auth()->login($user, true);
        });

        $redirectUrl = $team
            ? route('dashboard', ['current_team' => $team->slug])
            : route('home');

        return redirect()->intended($redirectUrl);
    }
}
