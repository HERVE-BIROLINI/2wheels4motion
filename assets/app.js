/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'bootstrap/dist/css/bootstrap.min.css';
import 'remixicon/fonts/remixicon.css';
import './styles/app.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

import './bootstrap';
import 'jquery';
import 'popper.js';
import 'bootstrap';
// start the Stimulus application
import { identifierForContextKey } from 'stimulus/webpack-helpers';
// import '@fortawesome/fontawesome-free/js/all.js';


document.addEventListener("DOMContentLoaded", function(event){
    /*
    // !!! NE SERT A RIEN !!!
    //------------------------
    // document.addEventListener('keyup keypress', function(e) {
    //     if(e.keyCode == 13) {
    //         e.preventDefault();
    //         return false;
    //     }
    // });
    //------------------------
    */
    
    // ** Vérifie l'existance d'une section (HTML) "masquée"
    //    contenant le fichier JSON transmis par PHP...
    //  * REGIONS *
    /*  FINALEMENT PAS UTILISE, GARDE SI S'AVERAIT UTILE...
    let regions=document.querySelector('#regions_json');
    // ... si données des Departments (nom+zip) trouvées...
    if(regions){
        // récupère les données JSON et les "stock" dans Javascript...
        // ... garde une version chaîne unique pour la recherche du ZIP à partir du NOM...
        regions=regions.textContent;
        while(regions.substr(0,1)=='\n' || regions.substr(0,1)==' '){
            regions=regions.substr(1);
        }
        // ... crée un tableau pour distinguer chaque commune pour l'affichage dans les listes
        var arRegions=regions.split(`$`);
    }
    */
    //  * DEPARTMENTS *
    /*  FINALEMENT PAS UTILISE, GARDE SI S'AVERAIT UTILE...
    let dpts=document.querySelector('#dpts_json');
    // ... si données des Departments (nom+zip) trouvées...
    if(dpts){
        // récupère les données JSON et les "stock" dans Javascript...
        // ... garde une version chaîne unique pour la recherche du ZIP à partir du NOM...
        dpts=dpts.textContent;
        while(dpts.substr(0,1)=='\n' || dpts.substr(0,1)==' '){
            dpts=dpts.substr(1);
        }
        // ... crée un tableau pour distinguer chaque commune pour l'affichage dans les listes
        var arDpts=dpts.split(`$`);
    }
    */
    //  * CITIES *
    let cities=document.querySelector('#cities_json');
    // ... si données des Communes (nom+zip) trouvées...
    if(cities){
        // récupère les données JSON et les "stock" dans Javascript...
        // ... garde une version chaîne unique pour la recherche du ZIP à partir du NOM...
        cities=cities.textContent;
        while(cities.substr(0,1)=='\n' || cities.substr(0,1)==' '){
            cities=cities.substr(1);
        }
        // ... crée un tableau pour distinguer chaque commune pour l'affichage dans les listes
        var arCities=cities.split(`$`)
            // .map((vItem)=>{return vItem.replace('\n', '');})
        //     .map((vItem)=>{return JSON.parse(vItem.replace('\n', ''));})
        ;
    }


    //-----------------------------------
    // *** DEBUT - Gestion du scroll ***
    //-----------------------------------
    var lastScrollTop=0;
    // - ... Récupère la hauteur par l'appel d'une fonction, besoin d'être dynamique...
    function getHeight(){return document.documentElement.scrollHeight;};
    function getBodyHeight(){return document.body.scrollHeight;};
    // - ... Récupère la hauteur 'interne' de la fenêtre de l'explorateur...
    function getInnerHeight(){return window.innerHeight?window.innerHeight:document.documentElement.clientHeight;};
    function getScrollTop(){return Math.max(document.body.scrollTop,document.documentElement.scrollTop);};
    // - Fonction d'analyse de la progression du scroll et de création des nouveaux éléments
    function scrollActing() {

        // * si le header venait à disparaître, le "fixe" en haut de page *
        let htmlHeader=document.querySelector("#section--header");
        let iHeaderHeight=Math.round(htmlHeader.offsetHeight);
        //
        if(Math.round(getScrollTop())>=iHeaderHeight * 0.66
            && (getHeight()-getInnerHeight())>Math.round(getScrollTop())+iHeaderHeight
        ){
            htmlHeader.setAttribute("style","position:fixed;top:0;z-index:99;padding-right:0em;");//opacity:0.95;
        }
        else if(Math.round(getScrollTop())<iHeaderHeight * 0.66){
            htmlHeader.removeAttribute("style");
        }

        // * si le bandeau "thématique" contient un "sous-menu"
        let htmlNavInPage=document.querySelector("#nav--inpage");
        if(htmlNavInPage){
            let htmlBanner=document.querySelector('.banner');
            if(htmlBanner){
                let iBannerHeight=htmlBanner.offsetHeight;
                if(getScrollTop()>=iBannerHeight-145){
                    htmlNavInPage.setAttribute("style","position:fixed;top:"+(htmlHeader.offsetHeight)+"px;z-index:9;padding-top:15px;padding-bottom:5px;");
                }else{
                    htmlNavInPage.removeAttribute("style");
                }
            }
        }

        // lorsque le slider arrive en bas...
        // if(Math.round(getScrollTop()+getInnerHeight())===Math.round(getHeight())){}
        // else{}

        // * analyse le sens de déroulement du scroll pour la gestion de l'affichage du bouton Uptotop *
        let currentScrollTop=window.pageYOffset //|| document.documentElement.scrollTop;
        let arUptotop=document.querySelectorAll('.uptotop');
        if (currentScrollTop > lastScrollTop && arUptotop){
            arUptotop.forEach(element => {
                element.style.display='';
            });
        } else {
            arUptotop.forEach(element => {
                element.style.display='none';
            });
        }
        lastScrollTop=currentScrollTop <= 0 ? 0 : currentScrollTop; // For Mobile or negative scrolling

    };
    // * Pose de l'espion du mouvement de scrolling *
    // (exception faite si absence de .Banner)
    let htmlBanner=document.querySelector('.banner');
    if(htmlBanner !== null){
        window.addEventListener('scroll',scrollActing);
    }
    //---------------------------------
    // *** FIN - Gestion du scroll ***
    //---------------------------------


    //--------------------------------------------------
    // *** DEBUT - Gestion de la position du footer ***
    //--------------------------------------------------
    if(getHeight()>getBodyHeight()){
        console.log('cherche....');
        let htmlFooter=document.querySelector('#app--footer');
        if(htmlFooter){
            htmlFooter.setAttribute("style","position:fixed;bottom:0;padding-right:0em;");
        }
        else if(Math.round(getScrollTop())<iHeaderHeight * 0.66){
            htmlFooter.removeAttribute("style");
        }
    }
    //------------------------------------------------
    // *** FIN - Gestion de la position du footer ***
    //------------------------------------------------

    
    //----------------------------------------------------------------------------------
    // *** DEBUT - Gestion du non-affichage d'une image suite à l'Upload en attente ***
    //----------------------------------------------------------------------------------
    // ... si bouton de recherche de fichiers trouvé...
    // recherche de tous les éléments Input et Select liés au nom de la commune...
    let htmlBrowseButton=document.querySelector("#browse");
    if(htmlBrowseButton){
        htmlBrowseButton.onchange=function(){
            let htmlImg=document.querySelector("#"+htmlBrowseButton.getAttribute('target_id'));
            // Si attribut spécifique permettant l'identification d'un élément HTML image...
            if(htmlImg){
                // ... le cache en attendant que la nouvelle image soit téléchargée pour l'afficher
                // htmlImg.src=...;
                htmlImg.style.display='none';
            }
        }
    }
    //--------------------------------------------------------------------------------
    // *** FIN - Gestion du non-affichage d'une image suite à l'Upload en attente ***
    //--------------------------------------------------------------------------------
    

    //------------------------------------------------------------------
    // *** DEBUT - Gestion de la sélection d'une City et de son Zip ***
    //------------------------------------------------------------------
    // ... si données des Communes (nom+zip) trouvées...
    // recherche de tous les éléments Input et Select liés au nom de la commune...
    let htmlCity=document.querySelectorAll(".inputcity");
    let htmlCities=document.querySelectorAll(".cities");
    if(htmlCity && htmlCities){
        // ... si en a trouvé, leur assigne les espions nécessaire à la prise en charge du dynamisme
        if(htmlCity){
            // boucle sur tous les éléments (Input Text) de la Class=inputcity trouvés...
            for(let inputcity of htmlCity){
                // ... leur "colle" un espion sur la saisie des touches
                inputcity.addEventListener('keyup', zipFilter);
                // ... et sur le changement de valeur
                inputcity.addEventListener('change', zipFilter);
            }
            // boucle sur tous les éléments (Select) de la Class=cities trouvés...
            for(let inputcities of htmlCities){
                // ... les "cache"
                inputcities.style.display='none';
                // ... leur "colle" un espion sur la sélection d'une commune dans la liste
                inputcities.addEventListener('change', cityChoosen);
            }
        }

        // * Fonction de gestion des entrées clavier de l'utilisateur dans le nom de la commune *
        function zipFilter(sCurrentEntry){

            // !!! NE SERT A RIEN !!!
            // // Si pas touche ENTER -> sort...
            // if(sCurrentEntry.key!=='Enter'){

            // ... la touche enfoncée...
            if(sCurrentEntry.type=='keyup' && sCurrentEntry.key){
                sCurrentEntry=sCurrentEntry.key;
                var iAsciiEntry=sCurrentEntry.charCodeAt();
            }
            // ... ou un changement d'une autre nature
            else{var bChange=true;}
            
            //
            let sCurrentValue=this.value;
            let htmlCity=this;//document.querySelector("#"+this.getAttribute('id').replace('city','zip'));
            // let htmlCity=document.querySelector("#"+this.getAttribute('id').replace('city','zip'));
            let htmlCities=document.querySelector("#"+this.getAttribute('id').replace('city','cities'));

            // code ascii acceptable :
            // 32       => ' '
            // 39       => \'
            // 45       => -
            // 65-90    => A-Z
            // 97-122   => a-z
            // Si la saisie est "cohérente"
            if((((iAsciiEntry==32 || iAsciiEntry==39 || iAsciiEntry==45
                            || (iAsciiEntry > 64 && iAsciiEntry < 91)
                            || (iAsciiEntry > 96 && iAsciiEntry < 123)
                            || iAsciiEntry==224// à
                            || iAsciiEntry==231// ç
                            || iAsciiEntry==232// è
                            || iAsciiEntry==233// é
                        )
                        && sCurrentEntry!="Backspace"
                    )
                    // // || (sCurrentEntry=="Backspace" && sCurrentValue.length!=0)
                    || bChange
                )
                && sCurrentValue!=''
            ){
                var arZip=[];
                for(let city of arCities){
                    if(sCurrentValue.toLowerCase()==city.substr(5,sCurrentValue.length).toLowerCase()
                        && !isNaN(parseInt(city.substr(0,5)))//isNumeric(city.substr(0,5))
                    ){
                        arZip.push(city.substr(5)+' ('+city.substr(0,5)+')');
                    }
                }
                // vide l'élément des sélections précédentes
                htmlCities.length=0;
                //...
                    htmlCities.style.display='none';
                if(arZip.length==0){
                    htmlCity.value=sCurrentValue.substr(0,sCurrentValue.length-1);
                    // document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value='';
                }
                else if(arZip.length==1){
                    // City Input
                    htmlCity.value=arZip[0].substr(0,arZip[0].length-8);
                    // ALL Zip Input
                    let htmlZips=document.querySelectorAll("."+htmlCity.getAttribute('id').replace('city','zip'));
                    for(let htmlZip of htmlZips){
                        htmlZip.value=arZip[0].substr(arZip[0].length-6,5);
                    }
                    document.querySelector("#"+htmlCity.getAttribute('id').replace('city','zip')).value=arZip[0].substr(arZip[0].length-6,5);
                    // document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value=arZip[0].substr(arZip[0].length-6,5);
                }
                else{
                    // tri dans l'ordre alphabétique et affiche la liste des possibilités
                    let newOption=new Option ('Sélectionnez votre commune', '');
                    htmlCities.options.add(newOption);
                    for (let city of arZip.sort()){
                        let newOption=new Option (city, city);
                        htmlCities.options.add(newOption);
                    }
                    // affiche la liste...
                    htmlCities.style.display='block';
                }
            }
            // ... sinon, ...
            else if(sCurrentEntry=="Backspace" && sCurrentValue.length==0)
            {
                htmlCities.style.display='none';
                // Reset ALL Zip Input
                let htmlZips=document.querySelectorAll("."+htmlCity.getAttribute('id').replace('city','zip'));
                for(let htmlZip of htmlZips){
                    htmlZip.value='';
                }
                document.querySelector("#"+htmlCity.getAttribute('id').replace('city','zip')).value='';
                // document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value='';
            }
            // ... si c'était une touche 'neutre'
            else if(sCurrentEntry == 'Control'
                    || sCurrentEntry == 'Shift'
                    || sCurrentEntry == 'Shift'
                    || sCurrentEntry == 'CapsLock'
                    || sCurrentEntry == 'Alt'
                    || sCurrentEntry == 'Tab'
                    || sCurrentEntry == 'AltGraph'
                    || sCurrentEntry == 'Meta'
                    || sCurrentEntry == 'ArrowLeft'
                    || sCurrentEntry == 'ArrowRight'
                    || sCurrentEntry == 'ArrowDown'
                    || sCurrentEntry == 'ArrowUp'
                    || sCurrentEntry == 'Home'
                    || sCurrentEntry == 'Insert'
                    || sCurrentEntry == 'Delete'
                    || sCurrentEntry == 'Escape'
                    || sCurrentEntry == 'Dead'
                    || sCurrentEntry == 'PageUp'
                    || sCurrentEntry == 'PageDown'
                ){
                // this.style.display='none';
            }
            // ... sinon rien d'autorisé, supprime la dernière saisie
            else if(sCurrentEntry!="Backspace"){
                htmlCity.value=sCurrentValue.substr(0,sCurrentValue.length-1);
            }
            else{
                // Reset ALL Zip Input
                let htmlZips=document.querySelectorAll("."+htmlCity.getAttribute('id').replace('city','zip'));
                for(let htmlZip of htmlZips){
                    htmlZip.value='';
                }
                document.querySelector("#"+htmlCity.getAttribute('id').replace('city','zip')).value='';
                // document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value='';
            }
            
        // }
        }

        // * Fonction de gestion d'une sélection dans la liste des communes *
        function cityChoosen(){
            let htmlCity=document.querySelector("#"+this.getAttribute('id').replace('cities','city'));
            //
            let htmlZip=document.querySelector("#"+this.getAttribute('id').replace('cities','zip'));
            let htmlZips=document.querySelectorAll("."+this.getAttribute('id').replace('cities','zip'));
            // console.log(htmlZips);
            // ... (RE)masque la liste des communes
            this.style.display='none';
            // ... affiche le Nom de la Commune choisie dans le Input TEXT
            htmlCity.value=this.value.substr(0,this.value.length-8);
            // htmlCity.value=this.value;
            // ... affiche de Code Postal de la Commune choisie dans le Input TEXT
            htmlZip.value=this.value.substr(this.value.length-6,5);
            for(let htmlZip of htmlZips){
                htmlZip.value=this.value.substr(this.value.length-6,5);
            }
        }
    }
    //----------------------------------------------------------------
    // *** FIN - Gestion de la sélection d'une City et de son Zip ***
    //----------------------------------------------------------------


    //-----------------------------------------------------------------
    // *** DEBUT - Gestion de la sélection d'une COMPANY existante ***
    //-----------------------------------------------------------------
    // action sur le bouton de choix d'une entreprise existante (MODAL, aussi...)
    let btnCompanies2Choice=document.querySelector('#btn--companies2choice');
    if(btnCompanies2Choice){
        //
        btnCompanies2Choice.addEventListener('click', showKnownCompanies);
        function showKnownCompanies(){
            document.getElementById('blk--company2create').style.display='none';
            document.getElementById('blk--companies2choice').style.display='block';
        }

        //
        let arBtnCompaniesKnown=document.querySelectorAll('.companyknown');
        for(let btnCompaniesKnown of arBtnCompaniesKnown){
            btnCompaniesKnown.addEventListener('click', showSubmitButton);
        }
        //
        function showSubmitButton(){
            document.querySelector('#btn--associate-company').style.pointerEvents="initial";
            document.querySelector('#btn--associate-company').style.opacity='initial';
        }

        // gestion du retour par le bouton d'annulation
        document.querySelector('#btn--companies-exit').onclick=goBackNewCompanyWithoutChoosen;
        function goBackNewCompanyWithoutChoosen(){
            document.getElementById('blk--company2create').style.display='';
            document.getElementById('blk--companies2choice').style.display='none';
        }

    }
    //---------------------------------------------------------------
    // *** FIN - Gestion de la sélection d'une COMPANY existante ***
    //---------------------------------------------------------------


    //----------------------------------------------------------------
    // *** DEBUT - Gestion de l'affichage du Tableau des FLATRATE ***
    //----------------------------------------------------------------
    //  ** Gestion DOM du Tableau de choix d'un tarif à la création d'un Tender **
    //      (les boutons + et - pour afficher/masquer le tableau des Forfaits)
    let divFlatrateToggle=document.querySelector('#blk--FlatrateToggles');
    if(divFlatrateToggle){
        //   * Gestion affichage/masquage de la liste de choix des forfaits *
        //   * (et des boutons de filtrage)                                 *
        // ... Les boutons
        let btnFlatrate_Expand=document.querySelector('#btn--expand');
        let btnFlatrate_Collapse=document.querySelector('#btn--collapse');
        // ... LE Label
        let lblFlatrates=document.getElementById("lbl--FlatrateToggles");
        // ... LA Table
        let tblFlatrates=document.getElementById("tbl--flatrates");
        //
        divFlatrateToggle.onclick=showhideFlatrates;
        //
        function showhideFlatrates(){
            if(getComputedStyle(tblFlatrates).display!="none"){
                tblFlatrates.style.display="none";
                //
                btnFlatrate_Expand.style.display="block";
                btnFlatrate_Collapse.style.display="none";
                //
                lblFlatrates.innerHTML="Afficher la liste";
            } else {
                tblFlatrates.style.display="block";
                //
                btnFlatrate_Expand.style.display="none";
                btnFlatrate_Collapse.style.display="block";
                //
                lblFlatrates.innerHTML="Masquer la liste";
            }
        };
        showhideFlatrates();
        
        /* ABANDONNE
        // ** Gestion des actions sur les boutons de filtrage **
        let arBtnFiltreRegion=document.getElementsByClassName("btn--filtre-region");
        let arBtnFiltreDept=document.getElementsByClassName("btn--filtre-dept");
        let arRowFlatrate=document.getElementsByClassName("row--flatrate");
        document.querySelector('#foot--flatrate').style.display="none";

        function showhideByRegions(){
            // let btnFiltreRegion= document.getElementsByClassName("dropdownMenuOffset--region");
            // btnFiltreRegion.innerText=btnFiltreRegion.textContent="Région ("+this.innerHTML+")";
            // console.log(btnFiltreRegion);
            let bOneMinimum=false;
            arRowFlatrate.forEach(element => {
                if(this.innerHTML=='TOUTES'){
                    element.style.display="flex";
                    bOneMinimum=true;
                }
                else if(element.className.includes(this.innerHTML)){
                    element.style.display="flex";
                    bOneMinimum=true;
                }
                else{
                    element.style.display="none";
                }
            });
            //
            if(bOneMinimum){
                document.querySelector('#head--flatrate').style.display="flex";
                document.querySelector('#foot--flatrate').style.display="none";
            }
            else{
                document.querySelector('#foot--flatrate').style.display="flex";
            }
        };
        function showhideByDepts(){
            // let btnFiltreDepartment= document.getElementsByClassName("dropdownMenuOffset--department");
            // btnFiltreDepartment.innerText=btnFiltreDepartment.textContent="Dépt. ("+this.innerHTML+")";       
            let bOneMinimum=false;
            arRowFlatrate.forEach(element => {
                if(this.innerHTML=='TOUTES'){
                    element.style.display="flex";
                    bOneMinimum=true;
                }
                else if(element.className.includes(this.innerHTML)){
                    element.style.display="flex";
                    bOneMinimum=true;
                }
                else{
                    element.style.display="none";
                }
            });
            //
            if(bOneMinimum){
                document.querySelector('#head--flatrate').style.display="flex";
                document.querySelector('#foot--flatrate').style.display="none";
            }
            else{
                document.querySelector('#foot--flatrate').style.display="flex";
            }
        };
        arBtnFiltreRegion.forEach(element => {
            element.onclick=showhideByRegions;
        });
        arBtnFiltreDept.forEach(element => {
            element.onclick=showhideByDepts;
        });
        */

        /*
        // ** gestion liste des départements suite à sélection d'une région **
    // console.log(arDpts);
    // console.log(document.querySelectorAll('.btn--filtre-region'));
        // document.querySelectorAll('.btn--filtre-region').forEach(element => {
        //     element.addEventListener('click', applyRegionFilterOnDpts);
        // });
        // function applyRegionFilterOnDpts(){
        //     // if(){

        //     // }
        // };

        // ** Gestion des actions sur les boutons de filtrage **
        // let arBtnFlatrates=document.querySelectorAll('.flatrate');

        // for(let btnFlatratesKnown of arBtnFlatratesKnown){
        //     btnFlatratesKnown.addEventListener('click', showSubmitButton);
        // }
        */
    }
    //--------------------------------------------------------------
    // *** FIN - Gestion de l'affichage du Tableau des FLATRATE ***
    //--------------------------------------------------------------


    //-----------------------------------------------------------------------
    // *** DEBUT - Gestion du calcul des Tender et affichage des Preview ***
    //-----------------------------------------------------------------------
    //  ** Gestion sélection d'un tarif à la création d'un Tender, remplissage des champs... **
    // ... Les boutons de radio
    let arRadioButtonsFlatrate=document.querySelectorAll('.rb--flatrate');
    // ... Le champs DESCRIPTION
    // let htmlRaceDesc=document.querySelector('#driver--comments[parent_id="rb--flatrate"]');
    // ... Le champs PRIX TOTAL
    let htmlRacePrice=document.querySelector('#driver--raceprice[parent_id="rb--flatrate"]');
    // ... Le champs PRIX UNITAIRE
    let htmlPricePerKm=document.querySelector('#driver--priceperkm[parent_id="rb--flatrate"]');
    //   * - Calcul suite au choix d'un forfait dans le Tableau - *
    if(arRadioButtonsFlatrate && htmlRacePrice ){// && htmlRaceDesc){
        arRadioButtonsFlatrate.forEach(flatrate => {
            flatrate.onclick=function(){
                // htmlRaceDesc.value=flatrate.getAttribute('label');
                // si choix du prix au kilomètre...
                if(flatrate.getAttribute('label').indexOf("par km") >= 0){
                    // ... le prix "au kilomètre" est renvoyé à l'Input "Prix du kilomètre"
                    htmlPricePerKm.value=flatrate.getAttribute('price');
                    // (traitement d'éventuels jumeaux de l'Input PricePerKm)
                    let arPricePerKm=document.querySelectorAll('[twin_id="'+htmlPricePerKm.id+'"]');
                    if(arPricePerKm){
                        arPricePerKm.forEach(html => {
                            html.innerHTML=htmlPricePerKm.value;
                        })
                    }
                    // ... le résultat du calcul est renvoyé à l'Input "Prix de la course"
                    calculTotal()
                    // les éléments spécifiques au calcul par km sont affichés
                    let htmlDivMultiplier=document.querySelector('#div--multiplier-TotalPrice');
                    if(htmlDivMultiplier){
                        htmlDivMultiplier.style.display="";
                    }
                    // les éléments spécifiques au calcul par km sont affichés
                    let htmlDivPreviewFlatrate=document.querySelector('#preview--flatrate-km');
                    console.log(htmlDivPreviewFlatrate);
                    if(htmlDivPreviewFlatrate){
                        htmlDivPreviewFlatrate.style.display="";
                    }
                    // les titre "FORFAIT" est caché sur le Preview
                    let htmlFlatrateTitle=document.querySelector('#preview--flatrate-title');
                    if(htmlFlatrateTitle){
                        htmlFlatrateTitle.style.display="none";
                    }
                // si choix d'un forfait...
                }else{
                    // ... le prix est renvoyé à l'Input "Prix de la course"
                    htmlRacePrice.value=flatrate.getAttribute('price');
                    // (traitement d'éventuels jumeaux de l'Input RacePrice)
                    let arInput_RacePrice_twin=document.querySelectorAll('[twin_id="'+htmlRacePrice.id+'"]');
                    if(arInput_RacePrice_twin){
                        arInput_RacePrice_twin.forEach(htmlTwin => {
                            htmlTwin.innerHTML=htmlRacePrice.value;
                        })
                    }
                    // les éléments spécifiques au calcul par km sont cachés
                    let htmlDivMultiplier=document.querySelector('#div--multiplier-TotalPrice');
                    if(htmlDivMultiplier){
                        htmlDivMultiplier.style.display="none";
                    }
                    // les éléments spécifiques au calcul par km sont affichés
                    let htmlDivPreviewFlatrate=document.querySelector('#preview--flatrate-km');
                    if(htmlDivPreviewFlatrate){
                        htmlDivPreviewFlatrate.style.display="none";
                    }
                    // les titre "FORFAIT" est caché sur le Preview
                    let htmlFlatrateTitle=document.querySelector('#preview--flatrate-title');
                    if(htmlFlatrateTitle){
                        htmlFlatrateTitle.style.display="";
                    }
                }
                // adapte le texte correspondant au Forfait choisi dans le tableau
                let htmlPreviewFlatrate=document.querySelector('#preview--flatrate');
                if(htmlPreviewFlatrate){
                    console.log(htmlPreviewFlatrate);
                    htmlPreviewFlatrate.innerHTML=flatrate.getAttribute('label');
                }
            }
        });
    }

    //  ** Gestion du TOTAL du tarif suite à la modification d'une "donnée"... **
    let htmlInput_TotalPrice=document.querySelector('[parent_class="input--multiplier-TotalPrice"]');
    //   * ... à la modification d'un des multiplicateurs ( * , multiply ) *
    let arInput_Multiplier4TotalPrice=document.querySelectorAll('.input--multiplier-TotalPrice');
    if(arInput_Multiplier4TotalPrice){
        arInput_Multiplier4TotalPrice.forEach(htmlInput => {
            htmlInput.onchange=function(){
                calculTotal();
                let arInput_Multiplier4TotalPrice_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
                if(arInput_Multiplier4TotalPrice_twin){
                    arInput_Multiplier4TotalPrice_twin.forEach(htmlTwin => {
                        if(this.value==''){
                            htmlTwin.innerHTML='??';
                            htmlTwin.style.color='red';
                        }else{
                            htmlTwin.innerHTML=this.value;
                            htmlTwin.style.color='';
                        }
                    })
                }
            }
        })
    }
    //   * ... à la modification d'un des contributeurs ( + , add ) *
    let arInput_2AddAtTotalPrice=document.querySelectorAll('.input--contributor-TotalPrice');
    if(arInput_2AddAtTotalPrice){
        arInput_2AddAtTotalPrice.forEach(htmlInput_2AddAtTotalPrice => {
            htmlInput_2AddAtTotalPrice.onchange=function(){
                console.log('--- chgt +');
                calculTotal();
                let arInput_2AddAtTotalPrice_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
                if(arInput_2AddAtTotalPrice_twin){
                    arInput_2AddAtTotalPrice_twin.forEach(htmlTwin => {
                        if(this.value==''){
                            htmlTwin.innerHTML='??';
                            htmlTwin.style.color='red';
                        }else{
                            htmlTwin.innerHTML=this.value;
                            htmlTwin.style.color='';
                        }
                    })
                }
            }
        })
    }

    //   * ... à la modification DIRECTE du TOTAL *
    if(htmlRacePrice){
        htmlRacePrice.onchange=function(){
            let arInput_RacePrice_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
            if(arInput_RacePrice_twin){
                arInput_RacePrice_twin.forEach(htmlTwin => {
                    if(this.value==''){
                        htmlTwin.innerHTML='??';
                        htmlTwin.style.color='red';
                    }else{
                        htmlTwin.innerHTML=this.value;
                        htmlTwin.style.color='';
                    }
                })
            }
        }
    }

    //   ** Les fonctions de calcul **
    function calculTotal(){
        let iResult=1;
        // les '*'
        arInput_Multiplier4TotalPrice.forEach(element => {
            if(element.value>0){
                iResult = iResult * element.value;
            }
        })
        // les '+'
        arInput_2AddAtTotalPrice.forEach(element => {
            if(element.value>0){
                iResult = parseInt(iResult) + parseInt(element.value);
            }
        })
        htmlInput_TotalPrice.value=iResult;
        // (traitement d'éventuels jumeaux du TOTAL)
        let arInput_TotalPrice=document.querySelectorAll('[twin_id="'+htmlInput_TotalPrice.id+'"]');
        if(arInput_TotalPrice){
            arInput_TotalPrice.forEach(html => {
                html.innerHTML=htmlInput_TotalPrice.value;
                html.style.color='';
            })
        }
    }

    // ... Le champs DepartureAtTime
    let htmlDepartureAtTime=document.querySelector('#driver--departureattime');
    if(htmlDepartureAtTime){
        htmlDepartureAtTime.onchange=function(){
            let arDepartureAtTime_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
            if(arDepartureAtTime_twin){
                arDepartureAtTime_twin.forEach(htmlTwin => {
                    htmlTwin.innerHTML=this.value;
                    htmlTwin.style.color='';
                })
            }
        }
    }
    // ... Le champs ArrivalAtTime
    let htmlArrivalAtTime=document.querySelector('#driver--arrivalattime');
    if(htmlArrivalAtTime){
        htmlArrivalAtTime.onchange=function(){
            let arArrivalAtTime_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
            if(arArrivalAtTime_twin){
                arArrivalAtTime_twin.forEach(htmlTwin => {
                    htmlTwin.innerHTML=this.value;
                    htmlTwin.style.color='';
                })
            }
        }
    }
    // ... Le champs TVA
    let htmlTva=document.querySelector('#driver--racetva');
    if(htmlTva){
        htmlTva.onchange=function(){
            let arTva_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
            if(arTva_twin){
                arTva_twin.forEach(htmlTwin => {
                    htmlTwin.innerHTML=this.options[this.selectedIndex].innerHTML;
                })
            }
        }
    }
    // ... Le champs Commentaires
    let htmlComments=document.querySelector('#driver--comments');
    if(htmlComments){
        htmlComments.onchange=function(){
            let arComments_twin=document.querySelectorAll('[twin_id="'+this.id+'"]');
            if(arComments_twin){
                arComments_twin.forEach(htmlTwin => {
                    htmlTwin.innerHTML=this.value;
                })
            }
        }
    }

    //---------------------------------------------------------------------
    // *** FIN - Gestion du calcul des Tender et affichage des Preview ***
    //---------------------------------------------------------------------


    //------------------------------------------------------------------------
    // *** DEBUT - Gestion de l'affichage des pages relatives aux onglets ***
    //------------------------------------------------------------------------
    // action sur le bouton de choix d'une entreprise existante
    let arRowTabtype=document.querySelectorAll('.row--tabtype');
    if(arRowTabtype.length>0){

        /*
        -- || ABANDONNEE (archivage dans la base) || --
        // ** Gestion du filtrage sur l'onglet courant **
        let htmlBtnSwitchArchive=document.querySelector('#btn--show-hide--archive');
        if(htmlBtnSwitchArchive){
            htmlBtnSwitchArchive.onclick=showhideArchive;
        }
        // * ne peut-être dans le IF ci-dessus (???) *
        function showhideArchive(){
            // recherche les 'lignes' de Class ARCHIVE, pour la Table affichée
            let btnTabTypeVisible=document.querySelector('.btn--tabtype[shown="Y"]');
            let trTabTypeVisible=document.querySelectorAll('.block--tabtype[parent_id="'+btnTabTypeVisible.id+'"] tbody tr.ARCHIVE');
            // observe l'état actuel
            let btnSwitch=document.querySelector('#btn--show-hide--archive');
            if(btnSwitch.innerHTML.includes('Afficher')){
            // if(this.innerHTML.endswith('toutes')){
                btnSwitch.innerHTML='<i class="far fa-eye"></i>&ensp;Récents seulement';
                //
                trTabTypeVisible.forEach(tr => {
                    tr.style.display="";
                })
            }
            else{
                btnSwitch.innerHTML='<i class="far fa-eye"></i>&ensp;Afficher tout';
                //
                trTabTypeVisible.forEach(tr => {
                    tr.style.display="none";
                })
            }
        }
        */
    
        // ** Pour chaque "Block" (Table... container Div...) multi-tab **
        arRowTabtype.forEach(rowTabtype => {
            // "colle" un espion à chaque élément associé
            let arBtnTabtype=document.querySelectorAll('.btn--tabtype[parent_id="'+rowTabtype.id+'"]');
            arBtnTabtype.forEach(btnTabType => {
                btnTabType.onclick=showhideBlockForSelectedTab;
                //
                // initialisation à l'ouverture de la page...
                if(btnTabType.getAttribute('default')!=null){
                    activeTabAndBlockOfSelectedTab(btnTabType);
                }
            });
        });

        // Gère l'affichage de la table et l'effet visuel de l'onglet
        function showhideBlockForSelectedTab(){
            activeTabAndBlockOfSelectedTab(this);
        }
        function activeTabAndBlockOfSelectedTab(btnTab2Activate){
            // récupère tous les onglets "voisins"...
            let arBtnTabtype=document.querySelectorAll('.btn--tabtype[parent_id="'+btnTab2Activate.getAttribute('parent_id')+'"]');
            // ... gère les effets visuels des onglets
            if(arBtnTabtype){
                arBtnTabtype.forEach(btnTabType => {
                    if(btnTabType==btnTab2Activate){
                        // test & background
                        btnTabType.style.color='black';
                        btnTabType.style.fontWeight ='bold';
                        btnTabType.style.backgroundColor='white';
                        // borders
                        btnTabType.style.borderBottom='0';
                        btnTabType.style.boxShadow='0 0 0 0, 0 -4px 4px 0 rgb(140, 140, 140)';
                        // ... en profite pour gérer l'affichage des tables associées
                        let tblChild=document.querySelector('.block--tabtype[parent_id="'+btnTab2Activate.id+'"]')
                        if(tblChild){
                            tblChild.style.display="";
                        }
                        btnTabType.setAttribute('shown','Y');
                        /* -- || ABANDONNEE (archivage dans la base) || --
                        // ... et l'affichage du bouton de filtrage, si données de plus d'1 an
                        if(typeof htmlBtnSwitchArchive !== 'undefined'){
                            let arRowArchive=document.querySelectorAll('.row--tab.ARCHIVE[parent_id="'+btnTab2Activate.id+'"]');
                            if(arRowArchive.length>0){
                                htmlBtnSwitchArchive.style.display="";
                            }
                            else{
                                htmlBtnSwitchArchive.style.display="none";
                            }
                        }
                        */
                    }
                    else{
                        // test & background
                        btnTabType.style.color='black';
                        btnTabType.style.fontWeight ='normal';
                        btnTabType.style.backgroundColor="rgb(233, 236, 239)";
                        // borders
                        btnTabType.style.borderBottom='0.5px solid black';
                        btnTabType.style.boxShadow='0 0 0 0';
                        // ... en profite pour gérer l'affichage des tables associées
                        let tblChild=document.querySelector('.block--tabtype[parent_id="'+btnTabType.id+'"]');
                        if(tblChild){
                            tblChild.style.display="none";
                        }
                        btnTabType.setAttribute('shown','N');
                    }
                })
            }
            /* -- || ABANDONNEE (archivage dans la base) || --
            if(htmlBtnSwitchArchive){
                htmlBtnSwitchArchive.innerHTML='<i class="far fa-eye"></i>&ensp;Récents seulement';
                // document.querySelector('#btn--show-hide--archive').innerHTML='<i class="far fa-eye"></i>&ensp;Récents seulement';
                showhideArchive();
            }
            */
        }
    }
    //----------------------------------------------------------------------
    // *** FIN - Gestion de l'affichage des pages relatives aux onglets ***
    //----------------------------------------------------------------------


    //----------------------------------------------------
    // *** DEBUT - Gestion de l'affichage des modales ***
    //----------------------------------------------------
    // DESCRIPTION DU FONCTIONNEMENT
    // - le bouton d'affichage d'une modale se doit d'être défini tel que :
    //      class:              'btn--showmodal'
    //      attr:               data_type=
    //      attr:               data_id=
    // - la modale associée se doit d'être définie tel que :
    //      id:                 'modal--{data_type}--{data_id}'
    // - le bouton de masquage d'une modale... :
    //      class:              'btn--hidemodal'
    //      attr:               parent_id={modal_id}='modal--{data_type}--{data_id}'
    // - tous les containers à masquer... :
    //      id et/ou class:     'div--2hide-ifmodal'
    let arBtnShowModal=document.querySelectorAll('.btn--showmodal');
    if(arBtnShowModal.length>0){
        arBtnShowModal.forEach(btn => {
            btn.onclick=showModal;
        })

        function showModal(){
            // Cache TOUS les containers pouvant exister
            let htmlMainItem2Hide=document.querySelector('#div--2hide-ifmodal');
            if(htmlMainItem2Hide){
                htmlMainItem2Hide.style.display="none";
            }
            let arMainItems=document.querySelectorAll('.div--2hide-ifmodal');
            if(arMainItems.length>0){
                arMainItems.forEach(htmMainItem => {
                    htmMainItem.style.display="none";
                });
            }
            // Affiche LA modale se devant d'exister selon les règles de conception
            let htmlModal2Show=document.querySelector('#modal--'+this.getAttribute('data_type')+'--'+this.getAttribute('data_id'));
            if(htmlModal2Show){
                htmlModal2Show.style.display="";
            }
            // Affiche le Bouton X de fermeture si celui-ci n'est pas défini sur la modale,
            // mais en position fixe dans le BASE.html.twig ...
            let htmlBtnClosingOfModal=document.querySelector('#btn--closing--modal--'+this.getAttribute('data_type')+'--'+this.getAttribute('data_id'));
            if(htmlBtnClosingOfModal){
                htmlBtnClosingOfModal.style.display="";
            }
            let htmlBtnClosing=document.querySelector('#btn--modal--closing');
            if(htmlBtnClosing){
                htmlBtnClosing.style.display="";
            }
            // Désactive le HEADER et le BANNER, pour interdire les actions qu'ils proposent
            let htmHeader=document.querySelector('#section--header');
            if(htmHeader){
                htmHeader.classList.add("disabledelements");
            }
            let htmlBanner=document.querySelector('.banner');
            if(htmlBanner){
                htmlBanner.classList.add("disabledelements");
            }
        }
    }
    // Gestion de l'action sur le bouton FERMER des Modal
    let arBtnHideModal=document.querySelectorAll('.btn--hidemodal');
    if(arBtnHideModal.length>0){
        arBtnHideModal.forEach(btn => {
            btn.onclick=hideModal;
        })

        function hideModal(){
            // RE-affiche TOUS les containers pouvant exister
            let htmlMainItem2Hide=document.querySelector('#div--2hide-ifmodal');
            if(htmlMainItem2Hide){
                htmlMainItem2Hide.style.display="";
            }
            let arMainItems=document.querySelectorAll('.div--2hide-ifmodal');
            if(arMainItems.length>0){
                arMainItems.forEach(htmMainItem => {
                    htmMainItem.style.display="";
                });
            }
            // RE-cache LA modale se devant d'exister selon les règles de conception
            let htmlModalShown=document.querySelector('#'+this.getAttribute('parent_id'));
            // ... si elle existe, la ferme...
            if(htmlModalShown){
                htmlModalShown.style.display="none";
            }
            // RE-cache le Bouton X de fermeture SI celui-ci n'est pas défini sur la modale,
            // mais en position fixe dans le BASE.html.twig ...
            let htmlBtnClosingOfModal=document.querySelector('#btn--closing--modal--'+this.getAttribute('data_type')+'--'+this.getAttribute('data_id'));
            if(htmlBtnClosingOfModal){
                htmlBtnClosingOfModal.style.display="none";
            }
            let htmlBtnClosing=document.querySelector('#btn--modal--closing');
            if(htmlBtnClosing){
                htmlBtnClosing.style.display="none";
            }
            // Re-active le HEADER et le BANNER, pour libérer les actions qu'ils proposent
            let htmHeader=document.querySelector('#section--header');
            if(htmHeader){
                htmHeader.classList.remove("disabledelements");
            }
            let htmlBanner=document.querySelector('.banner');
            if(htmlBanner){
                htmlBanner.classList.remove("disabledelements");
            }
        }
    }
    //--------------------------------------------------
    // *** FIN - Gestion de l'affichage des modales ***
    //--------------------------------------------------


    //----------------------------------------------------------------
    // *** DEBUT - Gestion de la copie d'un' ID dans le Clipboard ***
    //----------------------------------------------------------------
    // /!\ La copie dans le presse-papiers ne fonctionne pas si il y a enchaînement avec HREF
    let arHtml_CopyId2Clipboard=document.querySelectorAll('.html--copyidtoclipboard');
    if(arHtml_CopyId2Clipboard.length>0){
        arHtml_CopyId2Clipboard.forEach(html => {
            html.onclick=copyId2Clipboard;
        })

        function copyId2Clipboard(){
            navigator.permissions.query({name: "clipboard-write"}).then(result => {
                if (result.state == "granted" || result.state == "prompt") {
                    navigator.clipboard.writeText(this.id)
                    //
                    alert("Vous n'avez plus qu'à \"coller\" le n° de SIRET dans la zone à renseigner...");
                    window.open('https://avis-situation-sirene.insee.fr/', '_blank');
                }
            });
        }

    }
    //--------------------------------------------------------------
    // *** FIN - Gestion de la copie d'un' ID dans le Clipboard ***
    //--------------------------------------------------------------


    //-----------------------------------------------------------------
    // *** DEBUT - Gestion de la gestion d'affichage du bouton de  ***
    // *** création d'une association Raison Sociale / Taux de TVA ***
    //-----------------------------------------------------------------
    let html_SubmitBtn4Association=document.querySelector('#btn--associate--socialreason-tva');
    if(html_SubmitBtn4Association){

        document.querySelector('#select--socialreason').onclick=oneSelected;
        document.querySelector('#select--tva').onclick=oneSelected;

        //
        function oneSelected(){
            if(document.querySelector('#select--socialreason').value!=''
                && document.querySelector('#select--tva').value!=''
            ){
                showSubmitButton();
            }
        }
        //
        function showSubmitButton(){
            html_SubmitBtn4Association.style.pointerEvents="initial";
            html_SubmitBtn4Association.style.opacity='initial';
        }
    }
    //-----------------------------------------------------------------
    // *** FIN - Gestion de la gestion d'affichage du bouton de    ***
    // *** création d'une association Raison Sociale / Taux de TVA ***
    //-----------------------------------------------------------------


    //---------------------------------------------------------
    // ***      DEBUT - Gestion de l'affichage filtré      ***
    // *** (Liste déroulante, Tableau, ... tout conteneur) ***
    //---------------------------------------------------------
    let arSelectFilter=document.querySelectorAll('.select--filtered');
    if(arSelectFilter){
        arSelectFilter.forEach(element => {
            // Définit sélectionné par défaut l'option insélectionnable (titre colonne)
            element.selectedIndex=element.options[0]
            element.onchange=goFiltering;
        });

        function goFiltering(){
            funSelectOption(this);
        }
    }

    function funSelectOption(htmlSelectFilter){
        let sFilterSelected=htmlSelectFilter.options[htmlSelectFilter.selectedIndex];
        // gère l'affichage des lignes
        let arContainer2ApplyFilter=document.querySelectorAll('[parent_id="'+htmlSelectFilter.id+'"]');
        arContainer2ApplyFilter.forEach(element => {
            if(sFilterSelected.value==''
                || element.classList.contains(htmlSelectFilter.id+'_'+sFilterSelected.value)
                || element.classList.contains(htmlSelectFilter.id+'_')
            ){
                element.style.display="table-row";
            }
            else{
                element.style.display="none";

                // Si l'élément précédemment sélectionné est exclu par le filtre,
                // affiche le 1er élément de la liste filtrée (souvent un Placeholder)
                try {
                    let optionsProperty=element.parentNode.options
                    if(optionsProperty[element.parentNode.selectedIndex]==element){
                        element.parentNode.selectedIndex=0;
                    }
                } catch (error) {}
            }
        });
        // gère l'affichage de l'élément 'liste déroulante' qui fait office de titre de colonne...
        if(sFilterSelected.value==''){
            // ... sélection de 'ALL' si présent (value='')
            htmlSelectFilter.options[0].innerHTML=htmlSelectFilter.options[0].getAttribute('default_value');//'Région';
        }
        else{
            // ... sélection d'une Option
            htmlSelectFilter.options[0].innerHTML=htmlSelectFilter.options[0].getAttribute('default_value')+' : '+sFilterSelected.innerHTML;
        }
        htmlSelectFilter.selectedIndex=htmlSelectFilter.options[0];
        //
    }
    //---------------------------------------------------------
    // ***       FIN - Gestion de l'affichage filtré       ***
    // *** (Liste déroulante, Tableau, ... tout conteneur) ***
    //---------------------------------------------------------


    //-------------------------------------------------------------------------
    // *** DEBUT - Gestion de l'affichage du filtre, si sélection inversée ***
    //-------------------------------------------------------------------------
    let arSelectBackFilter=document.querySelectorAll('.select--filter--back');
    if(arSelectBackFilter){
        arSelectBackFilter.forEach(element => {
            element.onchange=function(){
                let sFilterSelected=this.options[this.selectedIndex];
                let sParent_id=sFilterSelected.getAttribute('parent_id');
                let sFilter=sFilterSelected.getAttribute('class');
                let htmlSelectFilter=document.querySelector('#'+sParent_id);
                //
                if(sParent_id){
                    let sSelectFilter=sFilter.substring(sParent_id.length+1)
                    // affiche le bon filtre correspondant à l'option choisi dans la liste filtrée
                    htmlSelectFilter.forEach(element => {
                        if(element.value==sSelectFilter){
                            htmlSelectFilter.selectedIndex=element.index;
                        }
                    });
                    //
                    if(funSelectOption){
                        funSelectOption(htmlSelectFilter);
                    }
                }
            }
        });
    }
    //-----------------------------------------------------------------------
    // *** FIN - Gestion de l'affichage du filtre, si sélection inversée ***
    //-----------------------------------------------------------------------


    //------------------------------------------------------
    // *** DEBUT - Gestion de l'activation par Checkbox ***
    //------------------------------------------------------
    // -- /!\ Plus utilisé, mais reste un fonctionnement intéressant /!\ --
    let arSwitchers=document.querySelectorAll('.cb--switcher');
    if(arSwitchers){
        arSwitchers.forEach(element => {
            element.addEventListener('click', switchStatus);
            switchStatusGO(element);
        })
        // 
        function switchStatus(){
            switchStatusGO(this);
        }
        function switchStatusGO(htmlCb_Switcher){
            // ... pour tous les "enfants" activables (propriété DISABLED)
            let arChildren_disabled=document.querySelectorAll('.switcher--disabled[parent_id="'+htmlCb_Switcher.id+'"]');
            arChildren_disabled.forEach(element => {
                element.disabled=htmlCb_Switcher.checked == false;
            });
            // ... pour tous les "enfants" sélectionnables (propriété CHECKED)
            let arChildren_checked=document.querySelectorAll('.switcher--checked[parent_id="'+htmlCb_Switcher.id+'"]');
            if(arChildren_checked){
                arChildren_checked.forEach(element => {
                    if(htmlCb_Switcher.checked || element.id.indexOf('_form_')<0){
                        element.checked=htmlCb_Switcher.checked;
                    }
                })
            }
        }
    }
    //----------------------------------------------------
    // *** FIN - Gestion de l'activation par Checkbox ***
    //----------------------------------------------------


    //-------------------------------------------------------------------------------------------
    // *** DEBUT - Gestion du choix d'une mise à Disposition lors de la création d'une Claim ***
    //-------------------------------------------------------------------------------------------
    let htmlSelectFlatrate=document.querySelector('#select--flatrate');
    if(htmlSelectFlatrate){
        htmlSelectFlatrate.onchange=function(){
            switchCheckedGO(this);
        }
        switchCheckedGO(htmlSelectFlatrate);

        // 
        function switchCheckedGO(htmlSelect_Switcher){
            // ... pour tous les "enfants" sélectionnables (propriété CHECKED)
            let arChildren_checked=document.querySelectorAll('.switcher--checked[parent_id="'+htmlSelect_Switcher.id+'"]');
            //
            if(arChildren_checked){
                arChildren_checked.forEach(element => {
                    if(htmlSelect_Switcher.selectedIndex !== 0){
                        element.checked=true;
                    }
                })
            }
        }
    }
    //-----------------------------------------------------------------------------------------
    // *** FIN - Gestion du choix d'une mise à Disposition lors de la création d'une Claim ***
    //-----------------------------------------------------------------------------------------


});
