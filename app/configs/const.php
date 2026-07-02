<?php  

define('root',$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']);
define('ASSETS',root.'/appeicg/assets/');
define('LINK',root.'/appeicg/');
define('HOME',root.'/appeicg');
define('APP_NAME','EICG');
define('TWO_PIP','/../../');
define('THREE_PIP','/../../'); 



// $sideBarData = [
//                 'test' =>[]
//             ];
            
CONST STATUT_INSCRIPTION = ['En attente','Confirmée','Annulée'];
CONST STATUT_PAIEMENT = ['En attente','Confirmé','Annulé'];
CONST MODE_PAIEMENT = ['Especes','Carte','Mobile money'];
CONST SEXEP = ['Mr','Mlle','Mme'];
const PIECES_DATA = ["CNI"=>"CNI","PASSEPORT" =>"PASSEPORT","CMU" =>"CMU","PERMIS" =>"PERMIS","CARTE CONSLAIRE" =>"CARTE CONSLAIRE","AUTRES" =>"AUTRES"];

CONST EXTENSION = ["jpg","png","jpeg","jfif","webp","svg","gif","bmp","ico","heic","heif"];
const PERIODE = "periode";
const RESERVATION = "reservation";
const OLD_URL = "old_url";
// CONST SEXE = ['G','F'];

CONST DAYS = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];


CONST MONTHS = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
];

