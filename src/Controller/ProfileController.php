<?php

namespace App\Controller;

use App\Entity\ClaimStatus;
use App\Entity\Picture;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Booking;
use App\Entity\Picturelabel;
use App\Entity\Socialreason;
use App\Entity\Tender;
use App\Entity\TenderStatus;
use App\Tools\RegexTools;
use App\Tools\UploadPictureTools;
use App\Twig\PictureTwig;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile", name="profile_")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("{id}", name="user", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function profileuser(User $user, MailerInterface $mailer): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$user){
            return $this->redirectToRoute('security_login');
        }
        //
        $entityManager = $this->getDoctrine()->getManager();

        // si retour, récupère "l'état" précédent pour réaffichage...
        if(isset($_POST['default_item'])){
            $default_item = $_POST['default_item'];
        }else{
            $default_item = null;
        }
        
        // *** Si retour après Submit ***
        // -----------------------------------
        //  ** Onglet Données Personnelles **
        $error_firstname=null;
        $error_lastname=null;
        $error_phone=null;
        $error_customer_road=null;
        $error_customer_city=null;
        //   * Analyse... *
        // ... prénom
        if(isset($_POST['firstname'])
            &&
            ($_POST['firstname']==''
                ||
                // ($_POST['firstname']!=$user->getFirstname()
                //     &&
                    !RegexTools::pattern_match($_POST['firstname'],'name')
                // )
            )
        ){
            $error_firstname=true;
        }
        // ... nom
        if(isset($_POST['lastname'])
            &&
            ($_POST['lastname']==''
                ||
                // ($_POST['lastname']!=$user->getLastname()
                //     &&
                    !RegexTools::pattern_match($_POST['lastname'],'name')
                // // !preg_match('@^[a-zA-Z \-]+@iD',$_POST['lastname'])
                // )
            )
        ){
            $error_lastname=true;
        }
        // ... téléphone
        if(isset($_POST['phone'])
            &&
            ($_POST['phone']==''
                ||
                // ($_POST['phone']!=$user->getPhone()
                //     &&
                    !RegexTools::pattern_match($_POST['phone'],'phone')
                // // !preg_match('@^0+[0-9]{9}+@iD',$_POST['phone'])
                // )
            )
        ){
            $error_phone=true;
        }
        // Regex / Pattern pour ROAD et CITY
        if(isset($_POST['customer_road']) &&
            ($_POST['customer_road']==''
                ||
                (isset($customer)
                    &&
                    $_POST['customer_road']!=$customer->getRoad()
                    &&
                    !RegexTools::pattern_match($_POST['customer_road'] )
                )
            )
        ){
            $error_customer_road=true;
        }
        if(isset($_POST['customer_city']) &&
            ($_POST['customer_city']==''
                ||
                (isset($customer)
                    &&
                    $_POST['customer_city']!=$customer->getCity()
                    &&
                    !RegexTools::pattern_match($_POST['customer_city'] )
                )
            )
        ){
            $error_customer_city=true;
        }

        //   * ... Si sans erreur => sauvegarde les modifications *
        if(count($_POST)>0 && isset($_POST['firstname']) && !$error_firstname && !$error_lastname && !$error_phone && !$error_customer_road && !$error_customer_city){
            //
            $user->setFirstname($_POST['firstname']);
            $user->setLastname($_POST['lastname']);
            $user->setPhone($_POST['phone']);
            //
            $customer=$user->getCustomer();
            if(!isset($customer)){
                $customer=new Customer();
                $user->setCustomer($customer);
            }
            //
            $customer->setRoad($_POST['customer_road']);
            $customer->setCity($_POST['customer_city']);
            $customer->setZip($_POST['customer_zip']);
            //
            $entityManager->persist($user);
            $entityManager->persist($customer);
            //
            $entityManager->flush();
            //
            // $this->addFlash('success', "Vos modifications ont bien été enregistrées.");
        }
        //  ** FIN - Onglet Données Personnelles **
        // -----------------------------------------

        // ---------------------------------------
        //  ** Onglet Données Professionnelles **
        $companyRepository=$entityManager->getRepository(Company::class);
        //
        $error_name=null;
        $error_siren=null;
        $error_nic=null;
        $error_road=null;
        // $error_zip=null;
        $error_city=null;
        //
        $error_vmdtrnumber=null;
        $error_vmdtrvalidity=null;
        $error_motomodel=null;
        $error_file=null;
        // ... important pour ne pas "parasiter" l'affichage des données de la T3P
        $company=null;
        // $bWithArchived=false;
        //   * Analyse... *
        // ... suite au choix d'une Company déjà "référencée"
        if(isset($_POST['companychoosen'])){
            // instancie une nouvelle valeur à l'objet Company,
            // TWIG réassignera les valeurs dans le formulaire...
            // $company=$companyRepository->findOneBy(['id'=>$_POST['companychoosen']]);
            $company=$entityManager->getRepository(Company::class)->findOneBy(['id'=>$_POST['companychoosen']]);
            //
            $this->addFlash('information', "Données d'une entreprise T3P référencée récupérées. Pensez à enregistrer vos modifications pour confirmer...");
            $bAllIsFine=false;
        }
        elseif(count($_POST)>1 && isset($_POST['siren'])){
            //
            // if(isset($_POST) && count($_POST)>0){
                // Défini les variables (pour défaut) à transmettre à TWIG
            $bAllIsFine=true;
            // }else{$bAllIsFine=false;}
            // -- Test les valeurs contenues dans les champs --
            //  - Données de la T3P -
            //  ---------------------
            //
            $driver=$user->getDriver();
            //  => NAME
            $name=$_POST['name'];
            if($name==''){$error_name=true;}
            //  => SIREN (nombre à 9 chiffres)
            if(isset($_POST['siren']) and (!is_numeric($_POST['siren']) || strlen($_POST['siren'])!=9)){
                $error_siren=true;
            }
            //  ... si 'format' correcte MAIS n'existe pas, crée une nouvelle Company...
            //  (sauf si un enregistrement avec le même nom existe déjà)
            elseif(isset($_POST['siren']) and $siren=$_POST['siren']
                    and !$companyRepository->findOneBy(['siren'=>$siren])
                )
            {
                // /!\ ATTENTION ! vérifie que le nom ne soit pas déjà utilisé (ai été changé avec le SIREN)
                if($companyRepository->findOneBy(['name'=>$name])){
                    //
                    $this->addFlash('danger', "Attention ! Ce nom existe déjà pour un autre n° de SIREN. Changement ignoré...");
                    $bAllIsFine=false;
                    $NoChangeAcceptable4T3P=true;
                }
                // ... si tout est OK, création nouvelle T3P et envoi du mail
                else{
                    $company=new Company;
                    $company->setSiren($siren);

                    // envoi d'un courriel à l'administrateur pour validation du compte Driver
                    $email=(new TemplatedEmail());
                    $email->from(new Address($user->getEmail(), '2Wheels4Motion - Annuaire Moto-taxi'))
                        ->to('twowheelsformotion@gmail.com')
                        ->subject("Validation du référencement d'une nouvelle T3P !")
                        ->text("Vérifier le SIREN ".$siren." pour l'entreprise T3P, ".
                                "dont la demande de référencement est faite par l'utilisateur ".
                                $user->getLastname()." ".$user->getFirstname()." (".$user->getId().")..."
                        )
                    ;
                    $mailer->send($email);
                    //
                    $this->addFlash('information', "Votre demande a été signifiée à l'administrateur par courriel");
                }
            }
            //  ... si 'format' correcte ET existe DEJA, sous un autre nom...
            elseif(isset($_POST['siren']) and $siren=$_POST['siren']
                    and $companyAllreadyExist=$companyRepository->findOneBy(['siren'=>$siren])
                    //
                    and $name
                    // and isset($_POST['name']) and $name=$_POST['name']
                    and $companyAllreadyExist->getName()!==$name
                    )
            {
                $NoChangeAcceptable4T3P=true;
                $this->addFlash('danger', "Attention ! Ce SIREN existe déjà pour une entreprise d'un autre nom. Changement ignoré...");
            }
            //  ... si 'format' du SIREN est correcte ET existe DEJA, au MEME nom
            //      => consécutif à la sélection d'une entreprise déjà référencée
            elseif($companyAllreadyExist and $companyAllreadyExist->getName()==$name
                    and $companyAllreadyExist!=$driver->getCompany()
                )
            {
                $company=$companyAllreadyExist;
                $this->addFlash('information', "Affiliation à une entreprise T3P déjà référencée prise en compte...");
            }
            //  ... si aucune modification en lien avec la T3P
            else{
                // $NoChangeAcceptable4T3P=true;
                $NoChangeDone4T3P=true;
                $company=$driver->getCompany();
            }
            
            // Si pas de "soucis" avec les données T3P, enregistre le reste des données T3P
            if(!isset($NoChangeAcceptable4T3P)){
                //  => NAME
                $company->setName($name);
                //  => NIC (nombre à 5 chiffres)
                $nic=$_POST['nic'];
                if($nic and (!is_numeric($_POST['nic']) || strlen($_POST['nic'])!=5)){
                    $error_nic=true;
                }
                elseif($nic){
                    $company->setNic($_POST['nic']);
                }
                //  => SOCIALREASON
                $company->setSocialreason($entityManager->getRepository(Socialreason::class)->findOneBy(['id'=>$_POST['socialreason']]));
                //  => ADRESSE
                $company->setRoad($_POST['road']);
                $company->setZip($_POST['zip']);
                $company->setCity($_POST['city']);
                //
                $entityManager->persist($company);
            }
            elseif(!isset($NoChangeDone4T3P)){
                $bAllIsFine=null;
                $this->addFlash('warning', "Pas de changement T3P prise en compte...");
            }
            elseif(isset($NoChangeDone4T3P)){
                $bAllIsFine=null;
            }
            //  - Données du pilote VMDTR -
            //  ---------------------------
            //  instanciation de l'objet image pour la carte VMDTR du pilote,
            $obPictureTwig=new PictureTwig($entityManager);
            //  si n'existe pas (??), instancie un nouvel
            if(!$obPicture=$obPictureTwig->getPictureOfDriverCard($user)){
                // ... Instancie un nouvel objet Picture
                $obPicture=new Picture;
            }
            // ... bon format, bonne taille...
            if($obPicture && isset($_FILES['file']) && $_FILES['file']['name']!=='' && $_FILES['file']['error']==0){
                // ... tente de l'UPLOADer...
                $obUploadPicture = new UploadPictureTools;
                $obPicturelabel_VMDTR=$entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'Carte pro. VMDTR - Face']);
                // Si UPLOAD réussi, met à jour les données dans la BdD
                if($obUploadPicture->UploadPicture($user, $obPicturelabel_VMDTR, $this->getParameter('asset_path_dev'))){
                    // ... Supprime le fichier précédent, si existe
                    if($obUploadPicture and $obUploadPicture->getDeletePreviousFile() and str_contains($obPicture->getPathname(),'user'))
                    {
                        if(file_exists($this->getParameter('asset_path_dev')
                                    .substr(strstr($obPicture->getPathname(),"/"),1)
                                )
                            )
                        {
                            unlink($this->getParameter('asset_path_dev')
                                .substr(strstr($obPicture->getPathname(),"/"),1)
                            );
                        }
                        if(file_exists($this->getParameter('asset_path_prod')
                                    .substr(strstr($obPicture->getPathname(),"/"),1)
                                )
                            )
                        {
                            unlink($this->getParameter('asset_path_prod')
                                .substr(strstr($obPicture->getPathname(),"/"),1)
                            );
                        }
                        $obUploadPicture->UploadPicture($user, $obPicturelabel_VMDTR, $this->getParameter('asset_path_dev'));
                        $this->addFlash('success', "L'ancienne image a été effacée au profit de la nouvelle...");
                    }
                    // modifie le chemin (devrait toujours être le même, puisque nomme le fichier)
                    $obPicture->setPathname($obUploadPicture->getPathname());
                    // Et au cas où nouvel objet image (???)...
                    //  ... précise le sujet de l'image
                    $obPicture->setPicturelabel($obPicturelabel_VMDTR);
                    //  ... précise le User propriétaire de l'image
                    $obPicture->setUser($user);
                    //
                    $entityManager->persist($obPicture);
                }
                else{
                    $this->addFlash('danger', "Echec de l'UPLOAD du fichier désigné !");
                    $bAllIsFine=false;
                }
            }
            // ... mauvais format ou trop "GROS" fichier...
            elseif(isset($_FILES['file']) and $_FILES['file']['name']!=='' and $_FILES['file']['error']!==0){
                $this->addFlash('danger', "Vous devez télécharger une copie de votre carte VMDTR, au format attendu, et ne \"pesant\" pas plus de 2MB.");
                $bAllIsFine=null;
            }
            //  => Associe la company au Driver
            if($company){
                $driver->setCompany($company);
            }

            /*  INUTILE ICI CAR N'EST PAS MODIFIABLE DANS LE TABLEAU DE BORD (DISABLED)
            // // => VMDTR_NUMBER (nombre à 11 chiffres)...
            // $vmdtr_number=$_POST['vmdtr_number'];
            // if(!is_numeric($vmdtr_number) || strlen($vmdtr_number)!=11){
            //     $error_vmdtrnumber=true;
            // }
            // //      ... et NON déjà référencé
            // elseif($DriverAlreadyExist=$entityManager->getRepository(Driver::class)->findOneBy(['vmdtr_number'=>$vmdtr_number])
            //         and $DriverAlreadyExist!=$driver
            //     )
            // {
            //     $error_vmdtrnumber=true;
            //     $this->addFlash('warning', "Il existe déjà un pilote référencé avec ce n° de carte pro. VMDTR");
            // }
            // elseif($vmdtr_number){
            //     $driver->setVmdtrNumber($vmdtr_number);
            // }
            */

            //   => VMDTR_VALIDITY
            $driver->setVmdtrValidity(new \DateTime($_POST['vmdtr_validity']));
            //   => MOTOMODEL
            $driver->setMotomodel($_POST['motomodel']);
            //
            $entityManager->persist($driver);
            //
            $entityManager->flush();

        }
        //   * ... Si sans erreur => sauvegarde les modifications *

        //  ** FIN - Onglet Données Professionnelles **
        // ---------------------------------------------
        else{$bAllIsFine=false;}

        // Message de confirmation à afficher
        if($bAllIsFine){
            $this->addFlash('success', "Vos modifications ont bien été enregistrées.");
        }
        
        //
        return $this->render('profile/user.html.twig', [
            // 'picture_portrait' => $entityManager->getRepository(Picture::class)->findOneBy(['picturelabel'=>$obPictureLabel_Portrait,'user'=>$user]),
            'controller_name' => 'ProfileController',
            //
            'default_item' => $default_item,
            // * Données Personnelles
            'error_firstname'     => $error_firstname,
            'error_lastname'      => $error_lastname,
            'error_phone'         => $error_phone,
            'error_customer_road' => $error_customer_road,
            'error_customer_city' => $error_customer_city,
            // * Données Professionnelles
            'company'             => $company,
            'error_name'          => $error_name,
            'error_siren'         => $error_siren,
            'error_nic'           => $error_nic,
            'error_road'          => $error_road,
            'error_city'          => $error_city,
            'error_vmdtrnumber'   => $error_vmdtrnumber,
            'error_vmdtrvalidity' => $error_vmdtrvalidity,
            'error_motomodel'     => $error_motomodel,
            'error_file'          => $error_file,
            //
            'allcompaniesknown' => $entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>true]),
        ]);
    }
    
    /**
     * @Route("{id}/changepicture", name="changepicture", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function changepicture(User $user
                                // , PicturelabelRepository $picturelabelRepository
                                // , EntityManagerInterface $entityManager
                                // , EntityManagerInterface $entityManagerInterface
    ): Response
    {
        // test si l'utilisateur N'est PAS encore identifié,
        // et s'il n'y a pas d'erreur de Route dans la barre d'adresse (tricheur !)...
        if(!$this->getUser() or $this->getUser()!==$user){
            return $this->redirectToRoute('security_login');
        }

        // 'drapeau' de levée d'actions, selon l'analyse
        $obUploadPicture=true;
        $bReturn2Profile=false;
        $bDeletePreviousFileIfExist=false;

        // instanciation du Manager de BdD
        $entityManager = $this->getDoctrine()->getManager();
        // recherche l'objet Picturelabel correspondant à l' "Avatar"
        $obPicturelabel_Portrait = $entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'avatar']);
        // recherche une éventuelle Picture déjà associée à User...
        if(!$obPicture=$entityManager->getRepository(Picture::class)
                ->findOneBy(['picturelabel'=>$obPicturelabel_Portrait,'user'=>$user])
        ){
            // ... sinon, instancie un nouvel objet
            $obPicture = new Picture;
        }
        
        // *** Si RETOUR dans le formulaire... ***
        if(isset($_POST['avatar'])){
            
            $obUploadPicture = new UploadPictureTools;
            
            //  ** File => un fichier choisi **
            if($_POST["avatar"]=="FILE" and $_FILES['file']['error']==0){
                // ----------v- A analyser lors de l'hébergement -v----------
                $obUploadPicture=$obUploadPicture->UploadPicture($user, $obPicturelabel_Portrait, $this->getParameter('asset_path_dev'));
                // $bError=$obUploadPicture->UploadPicture($user, $obPicturelabel_Portrait, $this->getParameter('asset_path_dev'));
                // ----------^- A analyser lors de l'hébergement -^----------
            }
            // ** Input => Choix d'un avatar prédéfini **
            elseif($_POST["avatar"]!=="FILE"){
                // demande la suppression de l'ancien fichier si existait
                $bDeletePreviousFileIfExist=true;
                // Change le chemin de l'objet picture
                $picture_PathName='build/images/avatar/'.$_POST['avatar'];
                // $obPicture->setPathname('build/images/avatar/'.$_POST['avatar']);
            }

            // ... si validation de l'image affichée, sans en avoir changé...
            if($_POST["avatar"]=="FILE" and $_FILES['file']['error']==4){
                // ... Retour au Profil, mais ne fait rien...
                $bReturn2Profile=true;
            }
            
            // Quelque soit le choix d'avatar, si pas de problème => effectue les traitements déduits par l'analyse
            // (met à jour les Tables de la BdD, supprime l'ancien fichier... puis retourne sur la page Profile...)
            if($obUploadPicture and !$bReturn2Profile){
                // supprime l'ancien fichier si existait
                // /!\ A faire avant de modifier le chemin mémorisé dans l'objet...
                if(($bDeletePreviousFileIfExist 
                        or (!is_null($obUploadPicture) and $obUploadPicture->getDeletePreviousFile())
                    )
                    and $obPicture->getPathname()
                    and str_contains($obPicture->getPathname(),'user')
                    and file_exists($obPicture->getPathname())
                ){
                    if(file_exists($this->getParameter('asset_path_dev')
                                .substr(strstr($obPicture->getPathname(),"/"),1)
                            )
                        )
                    {
                        unlink($this->getParameter('asset_path_dev')
                            .substr(strstr($obPicture->getPathname(),"/"),1)
                        );
                    }
                    if(file_exists($this->getParameter('asset_path_prod')
                                .substr(strstr($obPicture->getPathname(),"/"),1)
                            )
                        )
                    {
                        unlink($this->getParameter('asset_path_prod')
                            .substr(strstr($obPicture->getPathname(),"/"),1)
                        );
                    }
                    // unlink($obPicture->getPathname());
                }
                
                //
                if(isset($picture_PathName)){
                    $obPicture->setPathname($picture_PathName);
                }
                elseif(!is_null($obUploadPicture)){
                    $obPicture->setPathname($obUploadPicture->getPathName());
                }
                $obPicture->setPicturelabel($obPicturelabel_Portrait);
                $entityManager->persist($user);
                //
                $user->addPicture($obPicture);
                //  => écrit dans la Table User (et par 'cascade', Picture)
                $entityManager->flush();
                $bReturn2Profile=true;
            }

            // Si tout s'est bien passé, retourne à la page Profile_User
            if($bReturn2Profile=true){
                return $this->redirectToRoute('profile_user',['id'=>$user->getId()]);
            }
        }
        
        // *** Si ARRIVEE dans le formulaire, ou RETOUR suite erreur...
        return $this->render('profile/changepicture.html.twig', [
            'controller_name' => 'ProfileController',
            'controller_func' => 'ChangePicture',
        ]);
    }

    /**
     * @Route("/driver", name="driver")
     */
    public function profiledriver(): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        $user=$this->getUser();
        if($this->container->get('security.authorization_checker')
                                ->isGranted('ROLE_DRIVER')
            == false
            ||
            $user->getDriver()==null
            ||
            $user->getDriver()->getCompany()==null
        ){
            return $this->redirectToRoute('registration_driver', ['id' => $user->getId()]);
        }
        elseif(!$user){return $this->redirectToRoute('security_login');}

        // initialisation d' "ENTREE" des variables
        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{$bWithArchived=false;}
        if(isset($_GET['default_item'])){
            $default_item  = $_GET['default_item'];
        }else{$default_item=null;}
        
        //
        $entityManager = $this->getDoctrine()->getManager();    

        // ** Si retour dans le Controller **
        if(isset($_POST) and count($_POST)>0){
            // - récupère le nom de l'onglet 'affiché' pour prochaine affichage -
            // ------------------------------------------------------------------
            if(isset($_POST['default_item'])){
                $default_item=$_POST['default_item'];
            }else{$default_item=null;}
            //
            // - suite à demande d'affichage/masquage des archives -
            // -----------------------------------------------------
            if(isset($_POST['show-hide--archive'])){
                $bWithArchived=$_POST['witharchived']==0;
            }elseif(isset($_POST['witharchived'])){
                $bWithArchived=$_POST['witharchived'];
            }else{
                $bWithArchived=null;
            }
            
            //
            // - Onglet "Demandes reçues" -
            // ------------------------------
            // ... suite à action sur un bouton "switch" marqueur de demande LUE
            if(isset($_POST['driver_switchclaimstatus_viewed'])){
                $this->driver_switchclaimstatus_viewed($_POST['driver_switchclaimstatus_viewed']);
            }
            // ... suite à action sur un bouton "switch" d'ARCHIVAGE de Claim
            if(isset($_POST['driver_switchclaimstatus_archived'])){
                $this->driver_switchclaimstatus_archived($_POST['driver_switchclaimstatus_archived']);
            }
            // ... suite à action sur un bouton d'envoi d'un Tender
            if(isset($_POST['driver_tender_create'])){
                return $this->redirectToRoute('tender_create', [
                    'claim'=> $_POST['driver_tender_create'],
                    //
                    'witharchived'=> $bWithArchived,
                    'default_item'=> $default_item,
                ]);
            }
            // - Onglet "Devis envoyés" -
            // ------------------------
            // ... suite à action sur un bouton "switch" d'ARCHIVAGE de Tender
            if(isset($_POST['driver_switchtenderstatus_archived'])){
                $this->driver_switchtenderstatus_archived($_POST['driver_switchtenderstatus_archived']);
            }
            // - Onglet "Courses confirmées" -
            // -------------------------------
            // ... suite à action sur un bouton "switch" d'ARCHIVAGE de Booking
            if(isset($_POST['driver_switchBookingStatus_archived'])){
                $this->driver_switchBookingStatus_archived($_POST['driver_switchBookingStatus_archived']);
            }

            // - ... bouton partagé... -
            // ... suite à action sur un bouton "voir le devis" (colonne Détails)
            if(isset($_POST['driver_viewingtender'])
                &&
                $tender=$entityManager->getRepository(Tender::class)->findOneBy(['id'=>$_POST['driver_viewingtender']])
            ){
                return $this->redirectToRoute('tender_read', [
                    'controller_name' => 'ProfileController',
                    'controller_func' => 'profile_driver',
                    // 'function_name'   => 'ProfileCustomer',
                    //
                    'witharchived' => $bWithArchived,
                    'default_item' => $default_item,
                    //
                    'tender'   => $tender->getId(),
                    'driver'   => $tender->getDriver()->getId(),
                    'company'  => $tender->getDriver()->getCompany()->getId(),
                    'customer' => $tender->getClaim()->getCustomer()->getId(),
                    'claim'    => $tender->getClaim()->getId(),
                    'user'     => $user->getId(),
                ]);
            }
            elseif(isset($_POST['driver_viewingtender'])){
                $this->addFlash('danger', "Bizarre !! Le devis semble avoir disparu de la BdD...");
            }
        }else{
            // Contrôle d'intégrité de la base et correction si nécessaire
            if($driver=$user->getDriver()){
                $claims=$driver->getClaims();
                foreach($claims as $claim){
                    if(!$entityManager->getRepository(ClaimStatus::class)->findOneBy(['claim'=>$claim->getId(),'driver'=>$driver->getId()])){
                        $claimStatus=new ClaimStatus;
                        $claimStatus->setClaim($claim);
                        $claimStatus->setDriver($driver);
                        //
                        $entityManager->persist($claimStatus);
                        $entityManager->flush();
                    }
                }
            }
        }

        // ** Affiche/Ré-affiche la page **
        // ********************************
        return $this->render('profile/driver.html.twig', [
            'controller_name' => 'ProfileController',
            'controller_func' => $controller_func,
            'function_name'   => 'ProfileDriver',
            //
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
        // ********************************
    }
    
    public function driver_switchClaimStatus_viewed($claimStatus_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        //
        if($claimStatus=$entityManager->getRepository(ClaimStatus::class)->findOneBy(['id'=>$claimStatus_ID])){
            if($claimStatus->getIsread()){
                $claimStatus->setIsread(false);
            }else{
                $claimStatus->setIsread(true);
            }
            //
            $entityManager->persist($claimStatus);
            // "remplissage" de la BdD
            $entityManager->flush();
        }
    }
    public function driver_switchClaimStatus_archived($claimStatus_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        //
        if($claimStatus=$entityManager->getRepository(ClaimStatus::class)->findOneBy(['id'=>$claimStatus_ID])){
            if($claimStatus->getIsarchivedbydriver()){
                $claimStatus->setIsarchivedbydriver(false);
            }else{
                $claimStatus->setIsarchivedbydriver(true);
            }
            //
            $entityManager->persist($claimStatus);
            // "remplissage" de la BdD
            $entityManager->flush();
        }
    }
    public function driver_switchTenderStatus_archived($tenderStatus_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        //
        if($tenderStatus=$entityManager->getRepository(TenderStatus::class)->findOneBy(['id'=>$tenderStatus_ID])){
            if($tenderStatus->getIsarchivedbydriver()){
                $tenderStatus->setIsarchivedbydriver(false);
            }else{
                $tenderStatus->setIsarchivedbydriver(true);
            }
        }
        //
        $entityManager->persist($tenderStatus);
        // "remplissage" de la BdD
        $entityManager->flush();
    }
    public function driver_switchBookingStatus_archived($booking_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        $booking=$entityManager->getRepository(Booking::class)->findOneBy(['id'=>$booking_ID]);
        //
        if($booking->getIsarchivedbydriver()){
            $booking->setIsarchivedbydriver(false);
        }else{
            $booking->setIsarchivedbydriver(true);
        }
        //
        $entityManager->persist($booking);
        // "remplissage" de la BdD
        $entityManager->flush();
    }
/*
    public function driver_confirm($tender_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // lève le drapeau dans la table TenderStatus
        if($tenderStatus=$entityManager->getRepository(TenderStatus::class)->findOneBy(['tender'=>$tender_ID])){
            $tenderStatus->setIsbookingconfirmedbydriver(true);
            //
            $entityManager->persist($tenderStatus);
        }
        // crée un nouvel enregistrement dans la table Booking
        $booking=new Booking;
        $booking->setTender($entityManager->getRepository(Tender::class)->findOneBy(['id'=>$tender_ID]));
        //
        $entityManager->persist($booking);

        // "remplissage" de la BdD
        $entityManager->flush();





        // EMAIL ?!?!?!?

    }
*/

    /**
     * @Route("/customer", name="customer")
     */
    public function profilecustomer(): Response
    {
        // ** test si l'utilisateur N'est PAS encore identifié **
        $user=$this->getUser();
        if($user==null){
            return $this->redirectToRoute('security_login');
        }elseif($this->container ->get('security.authorization_checker')
                                ->isGranted('ROLE_CUSTOMER')
                == false
        ){
            return $this->redirectToRoute('profile_user', ['id' => $user->getId()]);
        }

        // initialisation d' "ENTREE" des variables
        if(isset($_GET['controller_func'])){
            $controller_func = $_GET['controller_func'];
        }else{$controller_func = null;}
        if(isset($_GET['witharchived'])){
            $bWithArchived = $_GET['witharchived'];
        }else{$bWithArchived=false;}
        if(isset($_GET['default_item'])){
            $default_item  = $_GET['default_item'];
        }else{$default_item=null;}

        //
        $entityManager = $this->getDoctrine()->getManager();    

        // ** Si retour dans le Controller **
        if(isset($_POST) and count($_POST)>0){

            // - récupère le nom de l'onglet 'affiché' pour prochaine affichage -
            // ------------------------------------------------------------------
            if(isset($_POST['default_item'])){
                $default_item=$_POST['default_item'];
            }else{$default_item=null;}
            //
            // - suite à demande d'affichage/masquage des archives -
            // -----------------------------------------------------
            if(isset($_POST['show-hide--archive'])){
                $bWithArchived=$_POST['witharchived']==0;
            }elseif(isset($_POST['witharchived'])){
                $bWithArchived=$_POST['witharchived'];
            }else{
                $bWithArchived=null;
            }

            //
            // - Onglet "Demandes envoyées" -
            // ------------------------------
            // ... suite à action sur un bouton "switch" d'ARCHIVAGE de Claim
            if(isset($_POST['customer_switchclaimstatus_archived'])){
                $this->customer_switchclaimstatus_archived($_POST['customer_switchclaimstatus_archived']);
            }
            // - Onglet "Devis reçus" -
            // ------------------------
            // ... suite à action sur un bouton "switch" d'ARCHIVAGE de Tender
            if(isset($_POST['customer_switchtenderstatus_archived'])){
                $this->customer_switchtenderstatus_archived($_POST['customer_switchtenderstatus_archived']);
            }
            // - Onglet "Courses confirmées" -
            // -------------------------------
            // ... suite à action sur un bouton "switch" d'ARCHIVAGE de Booking
            if(isset($_POST['customer_switchBookingStatus_archived'])){
                $this->customer_switchBookingStatus_archived($_POST['customer_switchBookingStatus_archived']);
            }

            // - ... bouton partagé... -
            // ... suite à action sur un bouton "voir le devis" (colonne Détails)
            if(isset($_POST['customer_viewingtender'])){
                $tender=$entityManager->getRepository(Tender::class)->findOneBy(['id'=>$_POST['customer_viewingtender']]);
                return $this->redirectToRoute('tender_read', [
                    // 'controller_name' => 'ProfileController',
                    'controller_func' => 'profile_customer',
                    // 'function_name'   => 'ProfileCustomer',
                    //
                    // 'comefrom'     => "profile_customer",
                    'witharchived' => $bWithArchived,
                    'default_item' => $default_item,
                    //
                    'tender'   => $tender->getId(),
                    'driver'   => $tender->getDriver()->getId(),
                    'company'  => $tender->getDriver()->getCompany()->getId(),
                    'customer' => $tender->getClaim()->getCustomer()->getId(),
                    'claim'    => $tender->getClaim()->getId(),
                    'user'     => $tender->getClaim()->getCustomer()->getUser()->getId(),
                ]);
            }
        }
        // ... si non, invoqué par le Driver à partir du courriel de Claim
        else{
            //
            // // Contrôle d'intégrité de la base et correction si nécessaire (??)
            // if($customer=$user->getCustomer()){
            //     $claims=$customer->getClaims();
            //     foreach($claims as $claim){
            //         if(!$entityManager->getRepository(ClaimStatus::class)->findOneBy(['claim'=>$claim->getId(),'driver'=>$driver->getId()])){
            //             $claimStatus=new ClaimStatus;
            //             $claimStatus->setClaim($claim);
            //             $claimStatus->setDriver($driver);
            //             //
            //             $entityManager->persist($claimStatus);
            //             $entityManager->flush();
            //         }
            //     }
            // }
        }
        
        // ** Affiche/Ré-affiche la page **
        // ********************************
        return $this->render('profile/customer.html.twig', [
            'controller_name' => 'ProfileController',
            'controller_func' => $controller_func,
            'function_name'   => 'ProfileCustomer',
            //
            'witharchived' => $bWithArchived,
            'default_item' => $default_item,
        ]);
        // ********************************
    }

    public function customer_switchClaimStatus_archived($claim_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        // Récupère les ClaimStatus relatifs à la Claim demandée
        $arClaimStatus=$entityManager->getRepository(ClaimStatus::class)->findBy(['claim'=>$claim_ID]);
        // Fonctionnement type interrupteur : observe l'état actuel du 1er enregistrement
        $Isarchivedbycustomer=$arClaimStatus[0]->getIsarchivedbycustomer()==false;
        // Itère sur chaque enregistrement 
        foreach($arClaimStatus as $claimStatus){
            $claimStatus->setIsarchivedbycustomer($Isarchivedbycustomer);
            //
            $entityManager->persist($claimStatus);
        }
        // "remplissage" de la BdD
        $entityManager->flush();
    }
    public function customer_switchTenderStatus_archived($status_ID){
        
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        $status=$entityManager->getRepository(TenderStatus::class)->findOneBy(['id'=>$status_ID]);

        if($status->getIsarchivedbycustomer()){
            $status->setIsarchivedbycustomer(false);
        }else{
            $status->setIsarchivedbycustomer(true);
        }
        //
        $entityManager->persist($status);
        // "remplissage" de la BdD
        $entityManager->flush();
    }
    public function customer_switchBookingStatus_archived($booking_ID){
        // Pour les lectures et enregistrements dans la BdD
        $entityManager=$this->getDoctrine()->getManager();
        $booking=$entityManager->getRepository(Booking::class)->findOneBy(['id'=>$booking_ID]);

        if($booking->getIsarchivedbycustomer()){
            $booking->setIsarchivedbycustomer(false);
        }else{
            $booking->setIsarchivedbycustomer(true);
        }
        //
        $entityManager->persist($booking);
        // "remplissage" de la BdD
        $entityManager->flush();
    }
    
}
