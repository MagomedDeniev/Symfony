/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single scss file (appFile.scss in this case)
import '../scss/app6.scss';

// Awesome fonts
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
const $ = require('jquery');
global.$ = global.jQuery = $;

// Bootstrap js
require('bootstrap');

// My scripts
import './scripts';
import './ajax';
import './share';
