<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\MainController;
use App\Models\Factory;
use App\Services\Service;
use Groupes;

class Controller extends MainController
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
        
        return $this->view('welcome', ['title' => 'Bienvenue']);
    }


     public function role()
    {
        if (Auth::hasGroupe(Groupes::SUPER)) {
            $user = (new Factory())->getSupUserWithFoction();
            
            $this->view('admins/role', ["users" => $user, 'title' => 'Gestion des roles']);

            return;
        }
        
        $user = (new Factory())->getUserWithFoction();

        return $this->view('admins/role', ["users" => $user, 'title' => 'Gestion des roles']);
    }


      /**
     * ------------------------------------------------------------------------
     * **********************************************************************
     * * SEXION POUR LES REQUESTS AJAX
     * SEXION POUR LES AJAX REQUESTS
     * **********************************************************************
     * --------------------------------------------------------------------------
     */
    
    
    public function test()
    {
        echo json_encode(["status" => "success", "message" => "test"]);
    }
    
}
