<?php

namespace App\Tools;

use App\Entity\Picturelabel;
use App\Entity\User;

class UploadPictureTools
{
    private string $pathName;
    private bool $deletePreviousFile;

    public function UploadPicture(User $user, Picturelabel $Picturelabel, String $root){
        $bError=false;
        // * déclaration/création du dossier destination *
        $sDestinationFolder='';
        // ----------v- A analyser lors de la publication -v----------
        $arFolder=[$root,'images/','uploads/','user/',$user->getId()];
        // $arFolder=[$this->getParameter('asset_path_prod'),'images/','uploads/','user/',$user->getId()];
        // ----------^- A analyser lors de la publication -^----------
        foreach($arFolder as $sFolder){
            $sDestinationFolder.=$sFolder;
            // ... si le dossier n'existe pas => le créer
            if(!file_exists($sDestinationFolder)){
                mkdir($sDestinationFolder);
            }
        }
        
        // * récupère les informations sur le fichier *
        // ... le nom (sans le chemin) de l'image dans la super globale
        $sFilePathInfo=pathinfo($_FILES['file']['name']);
        // // ... en déduit le nom (sans l'extension)
        // $sFileName=$sFilePathInfo['filename'];
        // ... en déduit son extension
        $sFileExtension=strtolower($sFilePathInfo['extension']);
        // ... parce que l'outil AURA DEJA copié le fichier dans une zone tampon...
        // ... ... récupère le chemin de cette zone tampon
        $sFileTmp=$_FILES['file']['tmp_name'];
        // ... vérifie que le type de fichier est autorisé (extension)
        $arExtensions=array('jpg','jpeg','png');
        // * si le fichier est bon, effectue la "copie" *
        // dd($arExtensions);
        if(in_array($sFileExtension, $arExtensions)){
            // Déplace le fichier de la zone tampon vers le chemin destination...
            $sFileFullDestination=$sDestinationFolder.'/'.$Picturelabel->getLabel().'.'.$sFileExtension;
            // Déplace le fichier...
            if(move_uploaded_file($sFileTmp,$sFileFullDestination)){
                // ... si le déplacement s'est bien dérouler...
                $this->setPathName('build/'.strstr($sFileFullDestination,'images/'));
                // $picture_PathName='build/'.strstr($sFileFullDestination,'images/');
            //     // $obPicture->setPathname('build/'.strstr($sFileFullDestination,'images/'));
                // ... demande la suppression de l'ancien fichier si existait
                $this->setdeletePreviousFile(true);
                // $bDeletePreviousFileIfExist=true;
            }
            else{
                $bError=true;
            }
        }
        // ... si le fichier n'a pas la bonne extension, lève le drapeau d'erreur
        else{
            $bError=true;
        }
        return $bError;
    }
    

    /**
     * Get the value of pathName
     */ 
    public function getPathName()
    {
        return $this->pathName;
    }

    /**
     * Set the value of pathName
     *
     * @return  self
     */ 
    public function setPathName($pathName)
    {
        $this->pathName = $pathName;

        return $this;
    }

    /**
     * Get the value of deletePreviousFile
     */ 
    public function getdeletePreviousFile()
    {
        return $this->deletePreviousFile;
    }

    /**
     * Set the value of deletePreviousFile
     *
     * @return  self
     */ 
    public function setdeletePreviousFile($deletePreviousFile)
    {
        $this->deletePreviousFile = $deletePreviousFile;

        return $this;
    }
}

?>
