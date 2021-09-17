<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\Customer;
// use App\Entity\Driver;
// use App\Entity\Driver;
use App\Entity\Picturelabel;
use App\Entity\Socialreason;
// use App\Form\ChangePwdFormType;:
// use App\Repository\PicturelabelRepository;
// use App\Repository\UserRepository;
use App\Tools\RegexTools;
use App\Tools\UploadPictureTools;
// use App\Twig\DriverTwig;
use App\Twig\PictureTwig;
// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
// use App\Twig\PictureTwig;
// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
// use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/profile", name="profile_")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("{id}", name="user", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function profileuser(User $user
                            , MailerInterface $mailer
                            // , EntityManagerInterface $entityManager
    ): Response
    {
        
        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$user){
            return $this->redirectToRoute('security_login');
        }
        //
        $entityManager = $this->getDoctrine()->getManager();


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
            $this->addFlash('success', "Vos modifications ont bien été enregistrées.");
        }
        //  ** FIN - Onglet Données Personnelles **
        // -----------------------------------------

        // ---------------------------------------
        //  ** Onglet Données Professionnelles **
        $companyRepository=$entityManager->getRepository(Company::class);
        // Défini les variables (pour défaut) à transmettre à TWIG
        $bAllIsFine=true;
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
        $obCompany=null;
        // $bWithArchived=false;
        //   * Analyse... *
        if(count($_POST)>1 && isset($_POST['siren'])){
            // -- Test les valeurs contenues dans les champs --
            //  - Données de la T3P -
            //  ---------------------
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
                    $obCompany=new Company;
                    $obCompany->setSiren($siren);

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
                $obCompany=$companyAllreadyExist;
                $this->addFlash('information', "Affiliation à une entreprise T3P déjà référencée prise en compte...");
            }
            //  ... si aucune modification en lien avec la T3P
            else{
                // $NoChangeAcceptable4T3P=true;
                $NoChangeDone4T3P=true;
                $obCompany=$driver->getCompany();
            }
            // ... Si pas de "soucis" avec les données T3P, enregistre le reste des données T3P
            if(!isset($NoChangeAcceptable4T3P)){
                //  => NAME
                $obCompany->setName($name);
                //  => NIC (nombre à 5 chiffres)
                $nic=$_POST['nic'];
                if($nic and (!is_numeric($_POST['nic']) || strlen($_POST['nic'])!=5)){
                    $error_nic=true;
                }
                elseif($nic){
                    $obCompany->setNic($_POST['nic']);
                }
                //  => SOCIALREASON
                $obCompany->setSocialreason($entityManager->getRepository(Socialreason::class)->findOneBy(['id'=>$_POST['socialreason']]));
                //  => ADRESSE
                $obCompany->setRoad($_POST['road']);
                $obCompany->setZip($_POST['zip']);
                $obCompany->setCity($_POST['city']);
                //
                $entityManager->persist($obCompany);
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
            //  => Sélection d'un fichier pour carte VMDTR --
            $obPictureTwig=new PictureTwig($entityManager);
            $obPicture=$obPictureTwig->getPictureOfDriverCard($user);
            // ... bon format, bonne taille...
            if(isset($_FILES['file']) and $_FILES['file']['name']!=='' and $_FILES['file']['error']==0){
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
                    $obPicture->setPathname($obUploadPicture->getPathname());
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
                $bAllIsFine=false;
            }
            //  => Associe la company au Driver
            if($obCompany){
                $driver->setCompany($obCompany);
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

            // Message de confirmation à afficher
            if($bAllIsFine){
                $this->addFlash('success', "Vos modifications ont bien été enregistrées.");
            }
        }
        //   * ... Si sans erreur => sauvegarde les modifications *

        //  ** FIN - Onglet Données Professionnelles **
        // ---------------------------------------------
        
        // ?? $obPictureLabel_Portrait=$entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'Avatar']);
        
        return $this->render('profile/user.html.twig', [
            // 'picture_portrait'  => $entityManager->getRepository(Picture::class)->findOneBy(['picturelabel'=>$obPictureLabel_Portrait,'user'=>$user]),
            'controller_name'   => 'ProfileController',
            // * Données Personnelles
            'error_firstname'       => $error_firstname,
            'error_lastname'        => $error_lastname,
            'error_phone'           => $error_phone,
            'error_customer_road'   => $error_customer_road,
            'error_customer_city'   => $error_customer_city,
            // * Données Professionnelles
            'error_name'            => $error_name,
            'error_siren'           => $error_siren,
            'error_nic'             => $error_nic,
            'error_road'            => $error_road,
            'error_city'            => $error_city,
            'error_vmdtrnumber'     => $error_vmdtrnumber,
            'error_vmdtrvalidity'   => $error_vmdtrvalidity,
            'error_motomodel'       => $error_motomodel,
            'error_file'            => $error_file,
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
    public function profiledriver(MailerInterface $mailer): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!isset($user) && !($user=$this->getUser() and true)){
            return $this->redirectToRoute('security_login');
        }
        if(!($driver=$user->getDriver() and true) || !$driver->getCompany()){
            return $this->redirectToRoute('registration_driver', ['id' => $user->getId()]);
        }

        $bWithArchived=false;
        // * Si retour dans le Controller suite à demande d'affichage/masquage des archives *
        if(isset($_POST['show-hide--archive'])){
            $bWithArchived=$_POST['witharchived']==0;
        }
        
        // ** Affiche/Ré-affiche la page **
        // ********************************
        return $this->render('profile/driver.html.twig', [
            'controller_name'   => 'ProfileController',
            'function_name'     => 'ProfileDriver',
            //
            'witharchived'  => $bWithArchived,
        ]);
        // ********************************
    }

    /**
     * @Route("/customer", name="customer")
     */
    public function profilecustomer(): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!isset($user) && !($user=$this->getUser() and true)){
            return $this->redirectToRoute('security_login');
        }
        if(!($driver=$user->getCustomer() and true)){
            return $this->redirectToRoute('profile_user', ['id' => $user->getId()]);
        }

        $bWithArchived=false;
        // * Si retour dans le Controller suite à demande d'affichage/masquage des archives *
        if(isset($_POST['show-hide--archive'])){
            $bWithArchived=$_POST['witharchived']==0;
        }
        
        // ** Affiche/Ré-affiche la page **
        // ********************************
        return $this->render('profile/customer.html.twig', [
            'controller_name'   => 'ProfileController',
            'function_name'     => 'ProfileCustomer',
            //
            'witharchived'      => $bWithArchived,
        ]);
        // ********************************
    }
    
}
