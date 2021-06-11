<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\User;
use App\Entity\Driver;
use App\Entity\Company;
use App\Form\ChangePwdFormType;
use App\Repository\PicturelabelRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/profile", name="profile_")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("/{id}", name="user", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function profileuser(User $user): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        $error_firstname=false;
        $error_lastname=false;
        $error_phone=false;
        // $entityManager = $this->getDoctrine()->getManager();

        if(isset($_POST['firstname'])){
            // test de la validité des nouvelles entrées
            if($_POST['firstname']!=$user->getFirstname()){
                if(!preg_match('@^[a-zA-Z \-]+@iD',$_POST['firstname'])){
                    $error_firstname=true;
                }
            }
            // test de la validité des nouvelles entrées
            if($_POST['lastname']!=$user->getLastname()){
                if(!preg_match('@^[a-zA-Z \-]+@iD',$_POST['lastname'])){
                    $error_lastname=true;
                }
            }
            // test de la validité des nouvelles entrées
            if($_POST['phone']!=$user->getPhone()){
                if(!preg_match('@^0+[0-9]{9}+@iD',$_POST['phone'])){
                    $error_phone=true;
                }
                // ...
            }
            //
            // $user->setFirstname($_POST['firstname']);
            // $user->setLastname($_POST['lastname']);
            // $user->setPhone($_POST['phone']);
            // dd($user);
        }

        return $this->render('profile/user.html.twig', [
            // 'controller_name'   => 'ProfileController',
            'error_firstname'   => $error_firstname,
            'error_lastname'    => $error_lastname,
            'error_phone'       => $error_phone,
        ]);
    }
    
    /**
     * @Route("/changepicture/{id}", name="changepicture", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function changepicture(User $user
                                , PicturelabelRepository $picturelabelRepository
                                ): Response
    {
        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        // recherche une éventuelle Picture déjà associée à User
        // sinon, instancie un nouvel objet
        if(!$picture=$user->getPicture()){
            $picture = new Picture;
        }

        // Si RETOUR de la page, avec un choix effectif d'image pour son Avatar
        if(isset($_POST['avatar'])){
            // 'drapeau' de levée d'erreur
            $bError=false;
            $bFile2Save=false;
            // instanciation du Manager de BdD
            $entityManager = $this->getDoctrine()->getManager();
            // ... recherche l'objet Picturelabel 'Avatar'
            $obPicturelabel = $picturelabelRepository->findOneBy(['label'=>'avatar']);

            // déclaration/création du dossier destination
            $sDestinationFolder='';
            $arFolder=[$this->getParameter('asset_path'),'images/','uploads/','user/',$user->getId()];
            foreach ($arFolder as $sFolder){
                $sDestinationFolder.=$sFolder;
                // ... si le dossier n'existe pas => le créer
                if(!file_exists($sDestinationFolder)) {
                    mkdir($sDestinationFolder);
                }
            }
            
            //  * File *
            // ... si le choix est un 'nouveau' fichier, défini
            if($_POST["avatar"]=="FILE" and $_FILES['file']['error']==0){
                // récupère les informations sur le fichier...
                // ... le nom (sans le chemin) de l'image dans la super globale
                $sFilePathInfo=pathinfo($_FILES['file']['name']);
                // ... ... en déduit le nom (sans l'extension)
                $sFileName=$sFilePathInfo['filename'];
                // ... ... en déduit son extension
                $sFileExtension=strtolower($sFilePathInfo['extension']);
                // ... parce que l'outil AURA DEJA copié le fichier dans une zone tampon...
                // ... ... récupère le chemin de cette zone tampon
                $sFileTmp=$_FILES['file']['tmp_name'];
                
                // ... vérifie que le type de fichier est autorisé (extension)
                $arExtensions=array('jpg','jpeg','png');
                // ... ... si le fichier est mauvais, lève le drapeau d'erreur
                if(!in_array(strtolower ($sFileExtension), $arExtensions)){
                    $bError=true;
                }
                // ... ... si le fichier est bon, lève le drapeau de copie
                else{
                    $bFile2Save=true;
                }
            }
            // ... si le choix est un avatar prédéfini...
            elseif($_POST["avatar"]!=="FILE"){
                // supprime l'ancien fichier si existait
                if($picture->getPathname()
                    and str_contains($picture->getPathname(),'user')
                    and file_exists($picture->getPathname())
                ){
                    unlink($picture->getPathname());
                }
                
                // Change le chemin de l'objet picture
                $picture->setPathname('build/images/avatar/'.$_POST['avatar']);
            }

            // **** TOUTES LES INFORMATIONS PARAISSENT CORRECTES ****
            // ** Déplace le fichier de la zone tampon vers le chemin destination **
            // dd($bError);
            if(!$bError and $bFile2Save){
                // supprime l'ancien fichier si existait
                if($picture->getPathname() 
                    and str_contains($picture->getPathname(),'user')
                    and file_exists($picture->getPathname())
                ){
                    unlink($picture->getPathname());
                }
                // 'force' un nom unique pour toutes les images utilisées comme Avatar
                $sFileName = 'Avatar';
                $sFileFullDestination=$sDestinationFolder.'/'.$sFileName.'.'.$sFileExtension;
                // Déplace le fichier...
                if(move_uploaded_file($sFileTmp,$sFileFullDestination)){
                    // ... si le déplacement s'est bien dérouler...
                    // ... défini l'objet Picture à sauver dans la BdD
                    $picture->setPathname(strstr($sFileFullDestination,'build/images/'));
                }
            }

            // Quelque soit le choix d'avatar, si pas de problème
            //  => met à jour les Tables de la BdD, puis retournesur la page Profile...
            if(!$bError){
                $picture->setPicturelabel($obPicturelabel);
                $entityManager->persist($user);
                $user->setPicture($picture);

                //  => écrit dans la Table User (et par 'cascade', Picture)
                $entityManager->flush();

                // Si tout s'est bien passé, retourne à la fiche User
                
                return $this->render('profile/user.html.twig', [
                    'error_firstname'   => false,
                    'error_lastname'    => false,
                    'error_phone'       => false,
                ]);
                // return $this->redirectToRoute('profile_user');
            }
        }

        return $this->render('profile/changepicture.html.twig', [
            'picture'   => $picture,
            // 'controller_name' => 'ProfileController',
        ]);
    }

    /**
     * @Route("/pwd/{id}", name="pwd", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function changePwd(Request $request
                            , User $id
                            , UserRepository $repository
                            // , GuardAuthenticatorHandler $guardHandler
                            // , LoginFormAuthenticator $authenticator
                            , UserPasswordEncoderInterface $passwordEncoder
                            // , AuthenticationUtils $authenticationUtils
                            ): Response{

        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);

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
    public function profiledriver(EntityManagerInterface $entityManager
    ): Response
    {

        // test si l'utilisateur N'est PAS encore identifié
        // if(!$this->getUser()){
        //     return $this->redirectToRoute('app_login');
        // }

        return $this->render('profile/driver.html.twig', [
            'controller_name' => 'ProfileController',
            'driver'=>$entityManager->getRepository(Driver::class)->findOneBy(['id'=>1]),
            'company'=>$entityManager->getRepository(Company::class)->findOneBy(['id'=>1]),

        ]);
    }

    /**
     * @Route("/customer", name="customer")
     */
    public function profilecustomer(): Response
    {

        dd(isset($_POST));
        if(isset($_POST)){
            dd(isset($_POST));
        }

        // test si l'utilisateur N'est PAS encore identifié
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        // au 1er passage, affiche la page du Profil et son formulaire
        return $this->render('profile/customer.html.twig', [
            'controller_name' => 'ProfileController',

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
