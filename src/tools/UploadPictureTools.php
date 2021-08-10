<?php

namespace App\Tools;

use App\Entity\Picturelabel;
use App\Entity\User;

class UploadPictureTools
{
    private string $pathName='';
    private bool $deletePreviousFile=false;

    public function UploadPicture(User $user, Picturelabel $Picturelabel, String $root){

        // * déclaration/création du dossier destination *
        $sDestinationFolder='';
        
        
        // ----------v- A analyser lors de l'hébergement -v----------
        $arFolder=array_merge([$root],
            $Picturelabel->getArray_imgfiles_user_path(),
            [$user->getId()]
        );
        // $arFolder=[$root,'images/','uploads/','user/',$user->getId()];
        // ----------^- A analyser lors de l'hébergement -^----------
        
        
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
        $sFileName=$sFilePathInfo['basename']; // => si n'impose pas pour nom, le sujet de l'image
        // ... en déduit son extension
        $sFileExtension=strtolower($sFilePathInfo['extension']);
        // ... parce que l'outil AURA DEJA copié le fichier dans une zone tampon...
        // ... ... récupère le chemin de cette zone tampon
        $sFileTmp=$_FILES['file']['tmp_name'];
        // ... vérifie que le type de fichier est autorisé (extension)
        $arExtensions=array('jpg','jpeg','png');
        // * si le fichier est bon, effectue la "copie" *
        if(in_array($sFileExtension, $arExtensions)){

            // Déplace le fichier de la zone tampon vers le chemin destination...
            $sFileFullDestination=$sDestinationFolder.'/'.$sFileName;
            // $sFileFullDestination=$sDestinationFolder.'/'.$Picturelabel->getLabel().'.'.$sFileExtension;
            
            if(move_uploaded_file($sFileTmp,$sFileFullDestination)){
                // ... si le déplacement s'est bien dérouler...
                $this->setPathName('build/'.strstr($sFileFullDestination,'images/'));
                // ... demande la suppression de l'ancien fichier si existait
                $this->setDeletePreviousFile(true);
                // ... retourne l'objet "Upload"
                return $this;
            }
            // ... si problème lors du déplacement...
            else{
                return false;
            }
        }
        // ... si le fichier n'a pas la bonne extension...
        else{
            return false;
        }
    }
    

    /**
     * Get the value of pathName
     */ 
    public function getPathName():string
    {
        return $this->pathName;
    }

    /**
     * Set the value of pathName
     *
     * @return  self
     */ 
    public function setPathName(string $pathName)
    {
        $this->pathName = $pathName;
        return $this;
    }

    /**
     * Get the value of deletePreviousFile
     */ 
    public function getDeletePreviousFile()
    {
        return $this->deletePreviousFile;
    }

    /**
     * Set the value of deletePreviousFile
     *
     * @return  self
     */ 
    public function setDeletePreviousFile($deletePreviousFile)
    {
        $this->deletePreviousFile = $deletePreviousFile;
        return $this;
    }
}

?>
