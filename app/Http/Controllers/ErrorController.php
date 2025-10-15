<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ErrorController extends Controller
{
    public function forbidden()
    {
        return Inertia::render('Errors/403');
    }
}