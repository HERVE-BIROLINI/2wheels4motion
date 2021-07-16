<?php

namespace App\Tools;

class RegexTools{
    
    private static $pattern_default="/[a-zA-Z\d\s\'\,\-àáâãäåçèéêëìíîïðòóôõöùúûüýÿ]/";
    private static $pattern_name="/[a-zA-Z\- ]/";
    private static $pattern_email="/[a-zA-Z]{1}+[a-zA-Z\s\-\_\.]+\@{1}[A-Za-z\d\-\_\.]+\.{1}+[A-Za-z]{2}/";
    private static $pattern_password="/[a-zA-Z\d\s\-\@\$\£\_]/";
    private static $pattern_phone="/0+[\d]{9}/";
    private static $pattern_zip="/[\d]{5}/";
    private static $pattern_siren="/[\d]{9}/";
    private static $pattern_nic="/[\d]{5}/";
    private static $pattern_vmdtr="/[\d]{11}/";
    
    public static function pattern_match(String $word, String $pattern=null){
        $echo=false;
        //
        if(!isset($pattern) or gettype($pattern)!=='string'){
            $pattern=self::$pattern_default;
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='name'){
            $pattern=self::$pattern_name;
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='email'){
            if(substr($word,-3,-2)=='.' and stristr($word,'@')){
                $echo=true;
            }
            else{
                $pattern=self::$pattern_email;
            }
        }
        elseif(gettype($pattern)=='string' and 
                (strtolower($pattern)=='password' or $pattern=='pwd')
            ){
            $pattern=self::$pattern_password;
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='phone' and gettype($word)=='string'){
            if(strlen($word)==10 and is_numeric($word)){
                $echo=true;
            }
            else{
                $pattern=self::$pattern_phone;
            }
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='zip' and gettype($word)=='string'){
            if(strlen($word)==5 and is_numeric($word)){
                $echo=true;
            }
            else{
                $pattern=self::$pattern_zip;
            }
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='siren' and gettype($word)=='string'){
            if(strlen($word)==9 and is_numeric($word)){
                $echo=true;
            }
            else{
                $pattern=self::$pattern_siren;
            }
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='nic' and gettype($word)=='string'){
            if(strlen($word)==5 and is_numeric($word)){
                $echo=true;
            }
            else{
                $pattern=self::$pattern_nic;
            }
        }
        elseif(gettype($pattern)=='string' and strtolower($pattern)=='vmdtr' and gettype($word)=='string'){
            if(strlen($word)==11 and is_numeric($word)){
                $echo=true;
            }
            else{
                $pattern=self::$pattern_vmdtr;
            }
        }
        else{
            $pattern=null;
        }
        //
        if((!isset($echo) or $echo==false)
            and gettype($word)=='string' and gettype($pattern)=='string'
            and strlen($word)>0
        ){
            $echo=true;
            while(($echo == true or $echo == 1) and strlen($word)>0){
                $echo=preg_match($pattern,substr($word,0,1));
                $word=substr($word,1);
            }
            if($echo!=0){
                $echo=true;
            }
            else{
                $echo=false;
            }
        }
        return $echo;
    }

}

?>