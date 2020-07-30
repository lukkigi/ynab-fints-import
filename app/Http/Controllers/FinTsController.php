<?php

namespace App\Http\Controllers;

use App\Facades\FinTsFacade;
use Illuminate\Http\RedirectResponse;

class FinTsController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function login()
    {
        return FinTsFacade::login(null);
    }

    /**
     * @return RedirectResponse
     */
    public function fetchAccounts()
    {
        return FinTsFacade::fetchAccounts();
    }

    /**
     * @return RedirectResponse
     */
    public function fetchTransactions()
    {
        return FinTsFacade::fetchTransactions();
    }
}
