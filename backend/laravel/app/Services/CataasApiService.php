<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class CataasApiService {
    public function cat()
    {
        return Http::withOptions([
            'sink' => 'storage/cat.jpg',
            'proxy' => env('PROXY_URL', 'http://127.0.0.1:2080'),
        ])->get('https://cataas.com/cat')->object();
    }
}