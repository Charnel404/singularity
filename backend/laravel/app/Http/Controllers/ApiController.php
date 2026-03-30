<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Services\YesNoApiService;
use App\Services\CataasApiService;

class ApiController extends Controller 
{

    public function index(YesNoApiService $yesNo, CataasApiService $cataas)
    {
        return Inertia::render("Welcome", [
            'canRegister' => Features::enabled(Features::registration()),
            'yesOrNo' => $yesNo->get(),
            'cataas' => $cataas->cat(),
        ]);
    }
}