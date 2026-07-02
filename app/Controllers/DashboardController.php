<?php
namespace App\Controllers;

use App\Core\MainController;

class DashboardController extends MainController
{

     /**
     * ------------------------------------------------------------------------
     * **********************************************************************
     * * SEXION POUR LES VUES
     * SEXION POUR LES VIEWS
     * **********************************************************************
     * --------------------------------------------------------------------------
     */

        public function index()
    {
        
        return $this->view('home/dashboard', ['title' => 'Dashboard']);
    }

    
}
