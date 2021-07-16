<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\User;
// use App\Entity\Driver;
use App\Entity\Company;
use App\Entity\Picturelabel;
use App\Form\ChangePwdFormType;
// use App\Repository\PicturelabelRepository;
use App\Repository\UserRepository;
use App\Tools\RegexTools;
use App\Tools\UploadPictureTools;
// use App\Twig\PictureTwig;
// use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
                            // , EntityManagerInterface $entityManager
    ): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$user){
            return $this->redirectToRoute('app_login');
        }

        //
        $error_firstname=false;
        $error_lastname=false;
        $error_phone=false;

        // * Si retour après Submit *
        if(isset($_POST['firstname'])){
            // test de la validité des nouvelles entrées...
            // ... prénom
            if($_POST['firstname']!=$user->getFirstname()){
                if(!RegexTools::pattern_match($_POST['firstname'],'name')){
                // if(!preg_match('@^[a-zA-Z \-]+@iD',$_POST['firstname'])){
                    $error_firstname=true;
                }
            }
            // ... nom
            if($_POST['lastname']!=$user->getLastname()){
                if(!RegexTools::pattern_match($_POST['lastname'],'name')){
                // if(!preg_match('@^[a-zA-Z \-]+@iD',$_POST['lastname'])){
                    $error_lastname=true;
                }
            }
            // ... téléphone
            if($_POST['phone']!=$user->getPhone()){
                if(!RegexTools::pattern_match($_POST['phone'],'phone')){
                // if(!preg_match('@^0+[0-9]{9}+@iD',$_POST['phone'])){
                    $error_phone=true;
                }
                // ...
            }

            // sauvegarde les modifications
            if(!$error_firstname and !$error_lastname and !$error_phone){
                $user->setFirstname($_POST['firstname']);
                $user->setLastname($_POST['lastname']);
                $user->setPhone($_POST['phone']);
                //
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            }
        }
        
        // ?? $obPictureLabel_Portrait=$entityManager->getRepository(Picturelabel::class)->findOneBy(['label'=>'Avatar']);
        
        return $this->render('profile/user.html.twig', [
            // 'picture_portrait'  => $entityManager->getRepository(Picture::class)->findOneBy(['picturelabel'=>$obPictureLabel_Portrait,'user'=>$user]),
            // 'controller_name'   => 'ProfileController',
            'error_firstname'   => $error_firstname,
            'error_lastname'    => $error_lastname,
            'error_phone'       => $error_phone,
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
            return $this->redirectToRoute('app_login');
        }

        // 'drapeau' de levée d'actions, selon l'analyse
        $bError=false;
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
                // ----------v- A analyser lors de la publication -v----------
                $bError=$obUploadPicture->UploadPicture($user, $obPicturelabel_Portrait, $this->getParameter('asset_path_dev'));
                // ----------^- A analyser lors de la publication -^----------
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
            if(!$bError and !$bReturn2Profile){
                
                // supprime l'ancien fichier si existait
                // /!\ A faire avant de modifier le chemin mémorisé dans l'objet...
                if(($bDeletePreviousFileIfExist 
                        or (!is_null($obUploadPicture) and $obUploadPicture->getdeletePreviousFile())
                    )
                    and $obPicture->getPathname()
                    and str_contains($obPicture->getPathname(),'user')
                    and file_exists($obPicture->getPathname())
                ){
                    // dd($obPicture->getPathname());
                    unlink($obPicture->getPathname());
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
        ]);
    }

    /**
     * @Route("{id}/pwd", name="pwd", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function changePwd(Request $request
                            , User $user
                            , UserRepository $repository
                            , UserPasswordEncoderInterface $passwordEncoder
                            // , GuardAuthenticatorHandler $guardHandler
                            // , LoginFormAuthenticator $authenticator
                            // , AuthenticationUtils $authenticationUtils
    ): Response
    {

        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser() or $this->getUser()!==$user){
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePwdFormType::class, $user);
        $form->handleRequest($request);

        $verify_password_error=false;

        if ($form->isSubmitted() && $form->isValid()){
            if(isset($_POST['password-old']) 
                // && preg_match("@^[a-z0-9]+@i", $_POST['password-old']==1)
                && password_verify ($_POST['password-old'] , $user->getPassword() )
            ){

                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                //
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_logout');
            }
            else{
                $verify_password_error=true;
            }
        }

        return $this->render('profile/changepwd.html.twig', [
            'changePwdForm'         => $form->createView(),
            'verify_password_error' => $verify_password_error
        ]);
    }

    /**
     * @Route("/driver", name="driver")
     */
    public function profiledriver(
        // EntityManagerInterface $entityManager
    ): Response
    {
// var_dump("<pre>");

        $entityManager = $this->getDoctrine()->getManager();
        $companies=$entityManager->getRepository(Company::class);

        // test si l'utilisateur N'est PAS encore identifié
        if(!$user=$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        // Récupère la Company si déjà définie...
        if($driver=$this->getUser()->getDriver()){
            $obCompany=$driver->getCompany();
        }
        // ... sinon (bien qu'impossible), renvoie vers la page de création du Driver
        else{
            return $this->redirectToRoute('registration_driver');
        }

        // * Si retour dans le Controller par le bouton 'Associer la société choisie' *
        if(isset($_POST['companychoosen'])){
            $obCompany=$companies->findOneBy(['id'=>$_POST['companychoosen']]);

            $this->addFlash('information', "Données d'une entreprise T3P référencée récupérées. Pensez à enregistrer vos modifications pour confirmer...");
        }
        // * Si retour dans le Controller par le bouton 'Enregistrer les modifications' *
        elseif(isset($_POST['siren'])){
            // Si le n° de SIREN n'existe pas, crée une nouvelle Company...
            if(!$obCompany=$companies->findOneBy(['siren'=>$_POST['siren']])){
            // if($obCompany=$companies->findOneBy(['siren'=>$_POST['siren']])){
            //     var_dump("<br> 1.2 - Modification d'une Company existante...");
            // }
            // else{
dd("développer l'envoi d'un email à l'administrateur pour vérification de la nouvelle Company !");
                $obCompany=new Company;
                $obCompany->setSiren($_POST['siren']);
            }
            //
            $obCompany->setName($_POST['name']);
            $obCompany->setNic($_POST['nic']);
            $obCompany->setRoad($_POST['road']);
            $obCompany->setZip($_POST['zip']);
            $obCompany->setCity($_POST['city']);
            //
            $entityManager->persist($obCompany);
            $entityManager->flush();
            
            // Associe la company au Driver
            $driver->setCompany($obCompany);
            $entityManager->flush();
            
            $this->addFlash('success', "Vos modifications ont bien été enregistrées.");
        }
        
        return $this->render('profile/driver.html.twig', [
            'controller_name' => 'ProfileController',
            //
            'company'   => $obCompany,
            //
            'allcompaniesknown'=>$entityManager->getRepository(Company::class)->findBy(['isconfirmed'=>true]),

        ]);
    }

    /**
     * @Route("/customer", name="customer")
     */
    public function profilecustomer(): Response
    {
        $error_customer_city=null;
        $error_customer_road=null;
        
        // test si l'utilisateur N'est PAS encore identifié
        if(!$user=$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        //
        if(isset($_POST) and $_POST!=null){
            // Regex / Pattern pour ROAD et CITY
            if(RegexTools::pattern_match($_POST['customer_road'] )){
                if(RegexTools::pattern_match($_POST['customer_city'] )){

                    // Pour les enregistrements dans la BdD
                    $entityManager = $this->getDoctrine()->getManager();
                    $customer=$user->getCustomer();
                    //
                    $customer->setRoad($_POST['customer_road']);
                    $customer->setCity($_POST['customer_city']);
                    $customer->setZip($_POST['customer_zip']);
                    //
                    $entityManager->persist($customer);
                    $entityManager->flush();

                }
                else{
                    $error_customer_city=true;
                }
            }
            else{
                $error_customer_road=true;
            }
        }
        
        // au 1er passage, affiche la page du Profil et son formulaire
        return $this->render('profile/customer.html.twig', [
            'controller_name'       => 'ProfileController',
            'error_customer_road'   => $error_customer_road,
            'error_customer_city'   => $error_customer_city,
        ]);
    }


//     /**
//      * @Route("/index", name="index")
//      */
//     public function index(
//                         // User $id
//                         // , UserRepository $repository
//                         ): Response
//     {
// // dd($id);
// // $user = $this->getDoctrine()->getRepository(User::class);
// // $user = $repository->find($id);

//         return $this->render('profile/index.html.twig', [
//             'controller_name' => 'ProfileController',

//         ]);
//     }
}
