<div class="sidebar sidebar-style-2" data-background-color="dark2">

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <!-- user connected -->
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                <div class="name-user">
                                    <span style="font-size: 16px; font-weight: bold;" class=""><?= shortName(auth()->user('nom')) ?></span>
                                </div>
                </div>
                
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            <?= (string) auth()->user("nom")?>
                            <span class="user-level text-success"><?= (string) auth()->user("id")?></span>
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>

                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            <li>
                                <a class="item-link" href="<?=  url('user.profile',['code' => auth()->user('id')])?>">
                                    <span class="link-collapse">Profile</span>
                                </a>
                            </li>
                            <li>
                                <a class="btn_deconnect" href="javascript:void();">
                                    <span class="link-collapse">Deconnexion</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <!-- menu lateral -->
            <!-- START ADMIN MENU -->

            <ul class="nav nav-primary">
                <li class="nav-item">
                    <a style="background: #8debfcf1;" class=""  href="<?= url('home') ?>">
                        <i class="fas fa-home"></i>
                    <p>ACCUEIL</p>
                    </a>
                </li>

                <!-- groupes()::ADMIN => -->

                <?php if(auth()->hasGroupe(groupes()::ADMIN)): ?>
                <li class="nav-item">
                    <a data-toggle="collapse" href="#admin">
                        <i class="fas fa-pen-square"></i>
                        <p>ADMINISTRATION</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="admin">
                        <ul class="nav nav-collapse">
                            <?php if(auth()->hasRole(roles()::ADMIN_H)): ?>

                            <li>
                                <a class="item-link" href="<?= route('admin.user') ?>">
                                    <span class="sub-item">Employés</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?= route('setting.fonctions') ?>">
                                    <span class="sub-item">Fonction</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if(auth()->hasRole(roles()::DASHBOARD_H)): ?>

                            <li>
                                <a class="item-link" href="<?= route('admin.role') ?>">
                                    <span class="sub-item">Permissions</span>
                                </a>
                            </li>
                             <li>
                                <a class="item-link" href="<?=  route('hotel.abonnement')?>">
                                    <span class="sub-item">Abonnement</span>
                                </a>
                            </li>
                            
                            <?php endif; ?>

                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- groupes()::COMPTABLE => -->
                <?php if(auth()->hasGroupe(groupes()::COMPTABLE)): ?>
                <li class="nav-item">
                    <a data-toggle="collapse" href="#comptable">
                        <i class="fas fa-pen-square"></i>
                        <p>COMPTABLE</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="comptable">
                        <ul class="nav nav-collapse">
                            <?php if(auth()->hasRole(roles()::COMPTATBLE_H)): ?>

                                <li>
                                <a class="item-link" href="<?=  route('comptable.caisse')?>">
                                    <span class="sub-item">Caisse</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?=  route('comptable.depense')?>">
                                    <span class="sub-item">Depenses</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?=  route('comptable.versement')?>">
                                    <span class="sub-item">Versements</span>
                                </a>
                            </li>

                            <?php endif; ?>

                            <?php if(auth()->hasRole(roles()::SALAIRE_H)): ?>

                           <li>
                                <a class="item-link" href="<?=  route('hotel.salaire')?>">
                                    <span class="sub-item">Salaires</span>
                              </a>
                            </li>
                            <?php endif; ?>

                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- groupes()::HOTEL => -->
                <?php if(auth()->hasGroupe(groupes()::HOTEL)): ?>
                <li class="nav-item">
                    <a data-toggle="collapse" href="#hotel">
                        <i class="fas fa-table"></i>
                        <p>GESTIONNAIRE</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="hotel">
                        <ul class="nav nav-collapse">
                            <?php if(auth()->hasRole(roles()::DEPENSE_H)) :?>
                            
                            <li>
                                <a class="item-link" href="<?= route('hotel.depenses') ?>">
                                    <span class="sub-item">Depenses</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            
                            <?php if(auth()->hasRole(roles()::MANAGER_H)): ?>
                            <li>
                                <a class="item-link" href="<?= route('hotel.chambres') ?>">
                                    <span class="sub-item">Chambres</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?= route('categorie.chambres')?>">
                                    <span class="sub-item">Type Chambre</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?= route('hotel.services')?>">
                                    <span class="sub-item">Services</span>
                                </a>
                            </li>
                            <?php endif; ?>
                           
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

            

                <!-- groupes()::RECEPTION => -->
                <?php if(auth()->hasGroupe(groupes()::RECEPTION)): ?>

                <li class="nav-item">
                    <a data-toggle="collapse" href="#reception">
                        <i class="fas fa-th-list"></i>
                        <p>RECEPTION</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="reception">
                        <ul class="nav nav-collapse">
                            <?php if(auth()->hasRole(roles()::RECEPTION_H)): ?>
                            <li>
                                <a class="item-link" href="<?= route('hotel.reservation') ?>">
                                    <span class="sub-item">Reservation</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?= route('hotel.add.reservation') ?>">
                                    <span class="sub-item">Enregistrer</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?=  route('client')?>">
                                    <span class="sub-item">Liste client</span>
                                </a>
                            </li>
                            <li>
                                <a class="item-link" href="<?=  route('versement')?>">
                                    <span class="sub-item">Ma caisse</span>
                                </a>
                            </li>
                            <?php endif; ?>


                        </ul>
                    </div>
                </li>
                <?php endif; ?>



                <!-- groupes()::PARAMETRE => -->
                <?php if(auth()->hasGroupe(groupes()::PARAMETRE)): ?>

                <li class="nav-item">
                    <a data-toggle="collapse" href="#parametre">
                        <i class="fas fa-pen-square"></i>
                        <p>PARAMETRE</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="parametre">
                        <ul class="nav nav-collapse">
                            <?php if(auth()->hasRole(roles()::PARAMETRE)): ?>

                            <li>
                                <a class="item-link" href="<?= route('setting.home') ?>">
                                    <span class="sub-item">Hotel</span>
                                </a>
                            </li>
                           
                            <?php endif; ?>


                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                


            </ul>

            <!-- END ADMIN SEXION -->
        </div>
    </div>
</div>