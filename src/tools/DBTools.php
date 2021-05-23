<?php

namespace App\tools;
use PDO;

class DBTools{
    protected string $sDB_NAME='2wheels4motion';
    private string $stQuery="mysql:host=127.0.0.1:8889;dbname=2wheels4motion";
    private string $sUser='root';
    private string $sPwd='root';
    //
    private PDO $obPDO;

    /** Instancie un objet de la Class PDO pour les paramètres
     *  de connexions donnés
     *  @return	$obPDO	PDO	instance of PDO Statement
    */
    public function init():PDO{
        return $this->obPDO=new PDO($this->stQuery,$this->sUser,$this->sPwd);
    }

    /** 
     *  @return	$obPDO	PDO	instance of PDO Statement
    */
    public function getPdo():PDO{
        return $this->obPDO;
    }

    /** @param	$sQuery     STRING	SQL request
     *  @param	$sTable     ARRAY	Array of Datas
     *  @return	$arFields	ARRAY	containing founded fields
    */
    public function execSqlQuery(string $sQuery,array $arDatas=[]){
        $stQuery=$this->obPDO->prepare($sQuery);
        $stQuery->execute($arDatas);
        return $stQuery->fetchall();
    }


    // /** @param	$sTable     STRING	Table to scan
    //  *  @return	$arFields	ARRAY	containing founded fields
    // */
    // public function funGetNameOfColumns($sTable):array{
    //     $stRequest=$this->execSqlQuery("SELECT column_name
    //                                     FROM information_schema.columns
    //                                     WHERE table_schema='".$this->sDB_NAME.
    //                                     "' AND table_name='$sTable'"
    //                                     );
    //     //
    //     if(isset($stRequest)and count($stRequest)>0){
    //         $arFields=[];
    //         foreach($stRequest as $fetch){
    //             $arFields[].= $fetch['column_name'];
    //         }
    //         return $arFields;
    //     }
    //     else{Echo'Pas d\'écriture dans la table '.$sTable.', ou elle n\'existe pas...';}
    // }

    
    // Fonctionnalité intéressante si couplé à la Class htmlGenerator
    //----------------------------------------------------------------
    // private array $arClass4Field=['picture_name'=>  ['file',''],
    //                                 'language'  =>  ['select'=>['*','HTML','CSS','Javascript','PHP']]
    //                             ];
    // public string $sLanguagesFieldName='language';
    // 
    // public function getClass4Field(string $sKey =null){
    //     if(isset($sKey) and isset($this->arClass4Field[$sKey]))
    //     {return $this->arClass4Field[$sKey];}
    //     elseif(!isset($sKey)){return $this->arClass4Field;}
    // }
    // public function getLanguages():array{
    //     $arLanguages=$this->getClass4Field($this->sLanguagesFieldName);
    //     return $arLanguages;
    // }
    //----------------------------------------------------------------
}

?>
