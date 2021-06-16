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



document.addEventListener("DOMContentLoaded", function(event) {
    // *** Gestion du scroll ***
    // -- ... Récupère la hauteur par l'appel d'une fonction, besoin d'être dynamique...
    function getHeight(){return document.documentElement.scrollHeight;};
    function getInnerHeight(){return window.innerHeight?window.innerHeight:document.documentElement.clientHeight;};
    function getScrollTop(){return Math.max(document.body.scrollTop,document.documentElement.scrollTop);};
    // -- Fonction d'analyse de la progression du scroll et de création des nouveaux éléments
    function scrollActing() {

        // si le header venait à disparaître, le "fixe" en haut de page.
        var htmlHeader=document.getElementById("header--header");
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
    // *** Pose de l'espion du mouvement de scrolling ***
    // window.addEventListener('scroll',scrollActing);
});
