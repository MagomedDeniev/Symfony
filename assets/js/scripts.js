// Prevent scroll

const $ = require("jquery");
let keys = {37: 1, 38: 1, 39: 1, 40: 1};

function preventDefault(e) {
    e.preventDefault();
}

function preventDefaultForScrollKeys(e) {
    if (keys[e.keyCode]) {
        preventDefault(e);
        return false;
    }
}

let supportsPassive = false;
try {
    window.addEventListener("test", null, Object.defineProperty({}, 'passive', {
        get: function () { supportsPassive = true; }
    }));
} catch(e) {}

let wheelOpt = supportsPassive ? { passive: false } : false;
let wheelEvent = 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel';

function disableScroll() {
    window.addEventListener('DOMMouseScroll', preventDefault, false); // older FF
    window.addEventListener(wheelEvent, preventDefault, wheelOpt); // modern desktop
    window.addEventListener('touchmove', preventDefault, wheelOpt); // mobile
    window.addEventListener('keydown', preventDefaultForScrollKeys, false);
}

function enableScroll() {
    window.removeEventListener('DOMMouseScroll', preventDefault, false);
    window.removeEventListener(wheelEvent, preventDefault, wheelOpt);
    window.removeEventListener('touchmove', preventDefault, wheelOpt);
    window.removeEventListener('keydown', preventDefaultForScrollKeys, false);
}

// Mobile navbar

const sideNav = document.getElementById('sideNav');
const sideNavOpener = document.getElementById('sideNavOpener');
const sideNavCloser = document.getElementById('sideNavCloser');
const sideNavLogoutCloser = document.getElementById('sideNavLogoutCloser');
const sideNavBack = document.getElementById('sideNavBack');

function openNav() {
    disableScroll();
    sideNav.style.transform = 'translateX(100%)';
    sideNavBack.style.opacity = '1';
    sideNavCloser.style.width = '100%';
}

function closeNav() {
    enableScroll();
    sideNav.style.transform = 'unset';
    sideNavBack.style.opacity = '0';
    sideNavCloser.style.width = '0';
}

if (sideNav) {
    sideNavOpener.addEventListener('click', openNav);
    sideNavCloser.addEventListener('click', closeNav);
    if (sideNavLogoutCloser) {
        sideNavLogoutCloser.addEventListener('click', closeNav);
    }
}

// MediaElement Player

require('mediaelement/build/mediaelement-and-player.min');

$(document).ready(function() {
    $('.audio-player audio').mediaelementplayer({
        success: function(player, node) {
            // Optional
            $(player).closest('.mejs__container').attr('lang', mejs.i18n.language());
            $('html').attr('lang', mejs.i18n.language());
            // More code
        },
        startVolume: 1,
        autoRewind: true,
        enableProgressTooltip: false,
        features: ['playpause','[feature_name]','current','progress','duration']
    })
});

// Comment reply

const commentWrite = document.querySelector('.md-comment-write');
const commentTextArea = document.getElementById('comment_message');
const commentReply = document.querySelectorAll('.comment-reply');
const replyTo = document.getElementById('comment_replyTo');
const replyFor = document.getElementById('comment_replyFor');
const commentReplyUser = document.querySelector('.md-comment-reply-user');
const replyingDelete = document.querySelector('.md-replying-delete');

if (replyingDelete) {
    replyingDelete.addEventListener('click', () => {
        replyTo.removeAttribute('value');
        replyFor.removeAttribute('value');
        commentWrite.classList.remove('md-replying');
    });
}

if (commentReply) {
    commentReply.forEach((reply) => {
        let replyUser = reply.querySelector('.reply-user');
        let replyComment = reply.querySelector('.reply-comment');
        reply.addEventListener('click', (reply) => {
            replyTo.value = replyUser.innerHTML;
            replyFor.value = replyComment.innerHTML;
            commentTextArea.focus();

            if (replyTo.value !== '') {
                commentWrite.classList.add('md-replying');
                commentReplyUser.innerHTML = replyTo.value;
            }
        })
    });
}

// Searcher

let searchInputs = document.querySelectorAll('.md-search-all-input');

const navbarSearch = document.getElementById('navbarSearch');
// const navbarSearchInput = document.getElementById('navbarSearchInput');
const navbarSearchOpener = document.getElementById('openNavbarSearch');
const navbarSearchCloser = document.getElementById('closeNavbarSearch');

function openNavbarSearch(){
    navbarSearchOpener.style.cssText = 'display:none !important';
    document.querySelector('.navbar-search-mobile').style.display = 'unset';
    document.querySelector('.navbar-search-mobile input').focus();
}

function closeNavbarSearch(){
    document.querySelector('.navbar-search-mobile').style.display = 'none';
    navbarSearchOpener.style.display = 'none';
}

if (navbarSearch) {
    navbarSearchOpener.addEventListener('click', openNavbarSearch);
    navbarSearchCloser.addEventListener('click', closeNavbarSearch);
    document.querySelector('.body-wrapper').addEventListener('click', closeNavbarSearch);
}

searchInputs.forEach((inputBox,key) => {
    inputBox.querySelector('.search_input').addEventListener('input',(input) => {
        inputBox.querySelector('.search_button').href = '/search/' + input.target.value.replace('#', '%23').replace('%', '%25');
    })

    inputBox.querySelector('.search_input').addEventListener('keyup',(event) => {
            if (event.keyCode === 13 || event.code === "Enter") {
                inputBox.querySelector('.search_button').click();
            }
        }
    );
});

// Enable tooltip
$(function () {
    $('[data-toggle="tooltip"]').tooltip({
        trigger : 'hover'
    })
});

// Image on change
if (document.querySelector('.custom-file-input')) {
    let fileInput = document.querySelector('.custom-file-input');
    if (document.getElementById('postImgOutput')) {
        let postImgOutput = document.getElementById('postImgOutput');
        fileInput.onchange = () => {
            postImgOutput.src = window.URL.createObjectURL(fileInput.files[0]);
            postImgOutput.style.marginBottom = '8px';
        };
    } else if (document.getElementById('output-content')) {
        let outputContent = document.getElementById('output-content');
        fileInput.onchange = () => {
            outputContent.style.backgroundImage = 'url(\'' + window.URL.createObjectURL(fileInput.files[0]) + '\')';
        };
    }
}

// Textarea autosize
if (document.querySelector('.md-auto-sizer')) {
    let textarea = document.querySelector('.md-auto-sizer');
    textarea.oninput = () => {
        textarea.style.height = textarea.scrollHeight + 2 + "px";
    };
}

// Auto close alerts
$(".md-alert-auto-hide").fadeTo(5000, 500).slideUp(500, function(){
    $(".md-alert-auto-hide").slideUp(500);
});

// Prevent username symbols
$('.username-input').on('keypress', function (event) {
    let regex = new RegExp("^[a-zA-Z0-9._]+$");
    let key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (!regex.test(key)) {
        event.preventDefault();
        return false;
    }
});

// Post collapse
const postCollapse = document.querySelectorAll('.post-collapse');

postCollapse.forEach((post) => {
    function openCollapsedPost() {
        this.style.display = 'none';
        document.getElementById('postCollapse' + postId).classList.remove('closed');
    }

    let postId = post.id.replace('postCollapse', '');

    if (post.querySelector('div').clientHeight > 50) {
        document.getElementById('postCollapseButton' + postId).style.display = 'block';
    }

    document.getElementById('postCollapseButton' + postId).addEventListener('click', openCollapsedPost);
});

// Modal change body color

// let observer = new MutationObserver((mutations) => {
//     mutations.forEach(function(mutation) {
//         if (mutation.attributeName === "class") {
//             if (mobileMedia.matches) {
//                 if ($(mutation.target).hasClass('modal-open')){
//                     observedBody.style.backgroundColor = '#141414';
//                 } else {
//                     observedBody.style.backgroundColor = '#282827';
//                 }
//             }
//         }
//     });
// });
//
// let mobileMedia = window.matchMedia("(max-width: 576px)")
// let observedBody = document.querySelector('body');
//
// observer.observe(observedBody, {
//     attributes: true
// });
