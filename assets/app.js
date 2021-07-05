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

// start the Stimulus application
import './bootstrap';
import 'jquery';
import 'popper.js';
import 'bootstrap';
import { identifierForContextKey } from 'stimulus/webpack-helpers';



document.addEventListener("DOMContentLoaded", function(event) {
    
    
    // ** Vérifie l'existance d'une section (HTML) "masquée"
    //    contenant le fichier JSON transmis par PHP...
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
    // ** Vérifie l'existance d'une section (HTML) "masquée"
    //    contenant le fichier JSON transmis par PHP...
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
    // - ... Récupère la hauteur par l'appel d'une fonction, besoin d'être dynamique...
    // function getHeight(){return document.documentElement.scrollHeight;};
    // function getInnerHeight(){return window.innerHeight?window.innerHeight:document.documentElement.clientHeight;};
    function getScrollTop(){return Math.max(document.body.scrollTop,document.documentElement.scrollTop);};
    // - Fonction d'analyse de la progression du scroll et de création des nouveaux éléments
    function scrollActing() {
        // si le header venait à disparaître, le "fixe" en haut de page.
        var htmlHeader=document.querySelector("#section--header");
        // var htmlHeader=document.getElementById("section--header");
        // htmlHeader.setAttribute("style","position:fixed;width:100%;top:0;z-index: 99;");
        var iHeaderHeight=Math.round(htmlHeader.offsetHeight);
        if(Math.round(getScrollTop())>=iHeaderHeight-(iHeaderHeight*0.25)){
            // htmlHeader.style.opacity=0.75;
            htmlHeader.setAttribute("style","position:fixed;width:100%;top:0;z-index: 99;opacity:0.75");
        }
        else if(Math.round(getScrollTop())<iHeaderHeight){
            // htmlHeader.style.opacity=1;
            htmlHeader.removeAttribute("style");
        }
        // lorsque le slider arrive en bas...
        // if(Math.round(getScrollTop()+getInnerHeight())===Math.round(getHeight())){}
        // else{}
    };
    // * Pose de l'espion du mouvement de scrolling *
    // window.addEventListener('scroll',scrollActing);
    //---------------------------------
    // *** FIN - Gestion du scroll ***
    //---------------------------------


    //------------------------------------------------------------------
    // *** DEBUT - Gestion de la sélection d'une City et de son Zip ***
    //------------------------------------------------------------------
    // ... si données des Communes (nom+zip) trouvées...
    // recherche de tous les éléments Input et Select liés au nom de la commune...
    let htmlCity=document.querySelectorAll(".inputcity");
    let htmlCities=document.querySelectorAll(".cities");
    if(cities && htmlCity){
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
                    htmlCity.value=arZip[0].substr(0,arZip[0].length-8);
                    document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value=arZip[0].substr(arZip[0].length-6,5);
                }
                else{
                    // tri dans l'ordre alphabétique et affiche la liste des possibilités
                    let newOption = new Option ('Sélectionnez votre commune', '');
                    htmlCities.options.add(newOption);
                    for (let city of arZip.sort()){
                        let newOption = new Option (city, city);
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
                document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value='';
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
                document.querySelector("."+htmlCity.getAttribute('id').replace('city','zip')).value='';
            }
        }

        // * Fonction de gestion d'une sélection dans la liste des communes *
        function cityChoosen(){
            let htmlCity=document.querySelector("#"+this.getAttribute('id').replace('cities','city'));
            //
            let htmlZip=document.querySelector("#"+this.getAttribute('id').replace('cities','zip'));
            let htmlZips=document.querySelectorAll("."+this.getAttribute('id').replace('cities','zip'));
            console.log(htmlZips);
            // ... (RE)masque la liste des communes
            this.style.display='none';
            // ... affiche le Nom de la Commune choisie dans le Input TEXT
            htmlCity.value=this.value.substr(0,this.value.length-8);
            // htmlCity.value=this.value;
            // ... affiche de Code Postal de la Commune choisie dans le Input TEXT
            htmlZip.value=this.value.substr(this.value.length-6,5);
            for(let htmlZip of htmlZips){
                htmlZip.value=this.value.substr(this.value.length-6,5);
                // htmlZip.value=cities.substr(cities.indexOf(htmlCity.value)-5,5);
            }
        }
    }
    //----------------------------------------------------------------
    // *** FIN - Gestion de la sélection d'une City et de son Zip ***
    //----------------------------------------------------------------


    //-----------------------------------------------------------------
    // *** DEBUT - Gestion de la sélection d'une COMPANY existante ***
    //-----------------------------------------------------------------
    // action sur le bouton de choix d'une entreprise existante
    let btnCompanies2Choice = document.querySelector('#btn--companies2choice');
    if(btnCompanies2Choice){

        btnCompanies2Choice.addEventListener('click', showKnownCompanies);
        function showKnownCompanies(){
            document.getElementById('blk--company2create').style.display = 'none';
            // document.querySelector('#blk--company2create').style.display = 'none';
            // document.getElementById('blk--driverdatas').setAttribute('class','col-12');
            document.getElementById('blk--companies2choice').style.display = 'block';
        }

        //
        let arBtnCompaniesKnown = document.querySelectorAll('.companyknown');
        for(let btnCompaniesKnown of arBtnCompaniesKnown){
            btnCompaniesKnown.addEventListener('click', showSubmitButton);
        }
        //
        function showSubmitButton(){
            document.querySelector('#btn--associate-company').style.pointerEvents="initial";
            document.querySelector('#btn--associate-company').style.opacity='initial';
        }
    }
    //---------------------------------------------------------------
    // *** FIN - Gestion de la sélection d'une COMPANY existante ***
    //---------------------------------------------------------------


    //-------------------------------------------------------
    // *** DEBUT - Gestion de la sélection d'un FLATRATE ***
    //-------------------------------------------------------
    // définition des variables...
    // ... les boutons...
    let btnFlatrateToggle = document.querySelector('#btn--FlatrateToggle');
    if(btnFlatrateToggle){

        // ** Gestion affichage/masquage de la liste de choix des forfaits **
        // ** (et des boutons de filtrage)                                 **
        // ... les boutons
        let btnFlatrate_Expand = document.querySelector('#btn--expand');
        let btnFlatrate_Collapse = document.querySelector('#btn--collapse');
        // ... LA Div
        let divFlatrates = document.getElementById("blk--flatrates");
        
        function showhideFlatrates(){
            if(getComputedStyle(divFlatrates).display != "none"){
                divFlatrates.style.display = "none";
                //
                btnFlatrate_Expand.style.display = "block";
                btnFlatrate_Collapse.style.display = "none";
            } else {
                divFlatrates.style.display = "block";
                //
                btnFlatrate_Expand.style.display = "none";
                btnFlatrate_Collapse.style.display = "block";
            }
        };
        btnFlatrateToggle.onclick = showhideFlatrates;
        showhideFlatrates();
        
        // ** Gestion des actions sur les boutons de filtrage **
        let arBtnFiltreRegion = document.getElementsByClassName("btn--filtre-region");
        let arBtnFiltreDept = document.getElementsByClassName("btn--filtre-dept");
        let arRowFlatrate = document.getElementsByClassName("row--flatrate");
        document.querySelector('#foot--flatrate').style.display = "none";

        // console.log(arBtnFiltreRegion);
        function showhideByRegions(){
            // let btnFiltreRegion= document.getElementsByClassName("dropdownMenuOffset--region");
            // btnFiltreRegion.innerText=btnFiltreRegion.textContent="Région ("+this.innerHTML+")";
            console.log(btnFiltreRegion);
            let bOneMinimum=false;
            arRowFlatrate.forEach(element => {
                if(this.innerHTML=='TOUTES'){
                    element.style.display = "flex";
                    bOneMinimum=true;
                }
                else if(element.className.includes(this.innerHTML)){
                    element.style.display = "flex";
                    bOneMinimum=true;
                }
                else{
                    element.style.display = "none";
                }
            });
            //
            if(bOneMinimum){
                document.querySelector('#head--flatrate').style.display = "flex";
                document.querySelector('#foot--flatrate').style.display = "none";
            }
            else{
                document.querySelector('#foot--flatrate').style.display = "flex";
            }
        };
        function showhideByDepts(){
            // let btnFiltreDepartment= document.getElementsByClassName("dropdownMenuOffset--department");
            // btnFiltreDepartment.innerText=btnFiltreDepartment.textContent="Dépt. ("+this.innerHTML+")";       
            let bOneMinimum=false;
            arRowFlatrate.forEach(element => {
                if(this.innerHTML=='TOUTES'){
                    element.style.display = "flex";
                    bOneMinimum=true;
                }
                else if(element.className.includes(this.innerHTML)){
                    element.style.display = "flex";
                    bOneMinimum=true;
                }
                else{
                    element.style.display = "none";
                }
            });
            //
            if(bOneMinimum){
                document.querySelector('#head--flatrate').style.display = "flex";
                document.querySelector('#foot--flatrate').style.display = "none";
            }
            else{
                document.querySelector('#foot--flatrate').style.display = "flex";
            }
        };
        arBtnFiltreRegion.forEach(element => {
            element.onclick = showhideByRegions;
        });
        arBtnFiltreDept.forEach(element => {
            element.onclick = showhideByDepts;
        });

        // ** gestion liste des départements suite à sélection d'une région **
// console.log(arDpts);
// console.log(document.querySelectorAll('.btn--filtre-region'));
        document.querySelectorAll('.btn--filtre-region').forEach(element => {
            element.addEventListener('click', applyRegionFilterOnDpts);
        });
        function applyRegionFilterOnDpts(){
            // if(){

            // }
        };




        // ** Gestion des actions sur les boutons de filtrage **
        let arBtnFlatrates = document.querySelectorAll('.flatrate');

        // for(let btnFlatratesKnown of arBtnFlatratesKnown){
        //     btnFlatratesKnown.addEventListener('click', showSubmitButton);
        // }
        //
        // function showSubmitButton(){
        //     document.querySelector('#btn--associate-company').style.pointerEvents="initial";
        //     document.querySelector('#btn--associate-company').style.opacity='initial';
        // }
    }
    //-----------------------------------------------------
    // *** FIN - Gestion de la sélection d'un FLATRATE ***
    //-----------------------------------------------------

});
