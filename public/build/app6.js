(self["webpackChunk"] = self["webpackChunk"] || []).push([["app6"],{

/***/ "./assets/js/ajax.js":
/*!***************************!*\
  !*** ./assets/js/ajax.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
__webpack_require__(/*! core-js/modules/web.timers.js */ "./node_modules/core-js/modules/web.timers.js");

__webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");

__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");

__webpack_require__(/*! core-js/modules/es.date.to-string.js */ "./node_modules/core-js/modules/es.date.to-string.js");

__webpack_require__(/*! core-js/modules/es.regexp.to-string.js */ "./node_modules/core-js/modules/es.regexp.to-string.js");

__webpack_require__(/*! core-js/modules/es.parse-int.js */ "./node_modules/core-js/modules/es.parse-int.js");

__webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");

__webpack_require__(/*! core-js/modules/es.string.split.js */ "./node_modules/core-js/modules/es.string.split.js");

__webpack_require__(/*! core-js/modules/es.array.includes.js */ "./node_modules/core-js/modules/es.array.includes.js");

__webpack_require__(/*! core-js/modules/es.string.includes.js */ "./node_modules/core-js/modules/es.string.includes.js");

__webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");

var axios = (__webpack_require__(/*! axios */ "./node_modules/axios/index.js")["default"]); // Add song to playlist or add post to bookmarks


var playlist = document.querySelectorAll('.playlist-toggle');
var bookmark = document.querySelectorAll('.bookmark-toggle');

function switcher(event) {
  var _this = this;

  event.preventDefault();
  var url = this.href;
  axios.get(url).then(function (response) {
    var status = String(response.data.response.status);
    status === 'added' ? _this.classList.add('added') : _this.classList.remove('added');
    _this.dataset.originalTitle = response.data.response.title;
    _this.style.pointerEvents = 'none';
    var alertBox = document.querySelector('main');
    var alertExist = document.querySelector('.md-alert');

    if (response.data.response.message) {
      var badge = '';

      if (response.data.response.status === 'added') {
        badge = 'success';
      } else {
        badge = 'danger';
      }

      var message = '<div class="md-alert md-alert-' + badge + ' md-box-mb">' + response.data.response.message + '</div>';

      if (alertExist) {
        alertExist.outerHTML = message;
      } else {
        alertBox.insertAdjacentHTML('afterbegin', message);
      }

      $(".md-alert").fadeTo(3000, 500).slideUp(500, function () {
        $(".md-alert").slideUp(500);
      });
    }

    setTimeout(function () {
      [_this].forEach(function (switcher) {
        switcher.style.pointerEvents = 'auto';
      });
    }, 100);
  });
}

playlist.forEach(function (playlist) {
  playlist.addEventListener('click', switcher);
});
bookmark.forEach(function (bookmark) {
  bookmark.addEventListener('click', switcher);
}); // Follow a user or unfollow from yourself

var follow = document.querySelectorAll('.follow-toggle');

function follows(event) {
  var _this2 = this;

  event.preventDefault();
  var url = this.href;
  axios.get(url).then(function (response) {
    var status = String(response.data.response.status);

    if (status === 'added') {
      _this2.classList.remove('btn-info');

      _this2.classList.add('btn-light');

      _this2.classList.add('followed');

      if (document.getElementById('profileFollowers')) {
        var followers = document.getElementById('profileFollowers').querySelector('.number').innerHTML;
        document.getElementById('profileFollowers').querySelector('.number').innerHTML = (parseInt(followers, 10) + 1).toString();
      }
    } else if (status === 'requested') {
      _this2.classList.remove('btn-info');

      _this2.classList.add('btn-light');

      _this2.classList.add('requested');
    } else {
      if (document.getElementById('profileFollowers') && !document.querySelector('.requested')) {
        var _followers = document.getElementById('profileFollowers').querySelector('.number').innerHTML;
        document.getElementById('profileFollowers').querySelector('.number').innerHTML = (parseInt(_followers, 10) - 1).toString();
      }

      _this2.classList.remove('btn-light');

      _this2.classList.remove('followed');

      _this2.classList.remove('requested');

      _this2.classList.add('btn-info');
    }

    _this2.dataset.originalTitle = response.data.response.title;
    _this2.style.pointerEvents = 'none';
    setTimeout(function () {
      [_this2].forEach(function (switcher) {
        switcher.style.pointerEvents = 'auto';
      });
    }, 100);
  });
}

var unfollow = document.querySelectorAll('.unfollow-toggle');
follow.forEach(function (follow) {
  follow.addEventListener('click', follows);
});

function unfollows(event) {
  event.preventDefault();
  var url = this.href;
  var followerBlock = 'u' + this.id.split('u').pop().split('t')[0] + 'l';
  var cancelButtonId = 'userUnfollow' + this.id.split('u').pop().split('t')[0];
  document.getElementById(cancelButtonId).click();
  axios.get(url).then(function (response) {
    var status = String(response.data.response.status);

    if (status === 'removed') {
      document.getElementById(followerBlock).remove();
    }
  });
}

unfollow.forEach(function (unfollow) {
  unfollow.addEventListener('click', unfollows);
}); // Accept or reject follow request

function rejectRequestFunction(event, requestResponse) {
  event.preventDefault();
  var url = event.target.href;
  var followerBlock = 'u' + event.target.id.split('u').pop().split('t')[0] + 'l';

  if (requestResponse === 'rejected') {
    var cancelButtonId = 'rejectRequest' + event.target.id.split('u').pop().split('t')[0];
    document.getElementById(cancelButtonId).click();
  }

  axios.get(url).then(function (response) {
    var request = String(response.data.response.request);

    if (request === requestResponse) {
      document.getElementById(followerBlock).remove();

      if (document.querySelector('.user-line') === null) {
        if (document.getElementById('requestsPaginator')) {
          document.location.reload();
        } else {
          document.getElementById('requestsList').remove();
          document.getElementById('requestsIsEmpty').style.display = 'block';
        }
      }
    }
  });
}

var acceptRequest = document.querySelectorAll('.accept-follow-request');
var rejectRequest = document.querySelectorAll('.reject-request-toggle');
acceptRequest.forEach(function (acceptRequest) {
  acceptRequest.addEventListener('click', function (event) {
    rejectRequestFunction(event, 'accepted');
  });
});
rejectRequest.forEach(function (rejectRequest) {
  rejectRequest.addEventListener('click', function (event) {
    rejectRequestFunction(event, 'rejected');
  });
}); // Make post featured

var featured = document.querySelectorAll('.featured-toggle');

function featuredPost(event) {
  var _this3 = this;

  event.preventDefault();
  var url = this.href;
  axios.get(url).then(function (response) {
    var status = String(response.data.response.status);
    status === 'added' ? _this3.classList.add('added') : _this3.classList.remove('added');
    setTimeout(function () {
      [_this3].forEach(function (switcher) {
        switcher.style.pointerEvents = 'auto';
      });
    }, 100);
  });
}

featured.forEach(function (featured) {
  featured.addEventListener('click', featuredPost);
}); // Likes

var like = document.querySelectorAll('.like-toggle');

function liker(event) {
  var _this4 = this;

  event.preventDefault();
  var url = this.href;
  axios.get(url).then(function (response) {
    var status = String(response.data.response.status);
    status === 'added' ? _this4.classList.add('added') : _this4.classList.remove('added');

    function contains(value) {
      return value.includes('post') ? "post" : "comment";
    }

    var likeCounter = contains(_this4.id) + '-like-counter-' + _this4.id.replace(contains(_this4.id) + '-like-', '');

    var currentLikes = parseInt(document.getElementById(likeCounter).innerHTML);

    if (status === 'added') {
      if (contains(_this4.id) === 'comment') {
        if (currentLikes === 0) {
          document.getElementById(likeCounter).parentElement.style.display = 'unset';
        }
      }

      document.getElementById(likeCounter).innerHTML = currentLikes + 1;
    } else {
      if (contains(_this4.id) === 'comment') {
        if (currentLikes === 1) {
          document.getElementById(likeCounter).parentElement.style.display = 'none';
        }
      }

      document.getElementById(likeCounter).innerHTML = currentLikes - 1;
    }

    setTimeout(function () {
      [_this4].forEach(function (switcher) {
        switcher.style.pointerEvents = 'auto';
      });
    }, 100);
  });
}

like.forEach(function (like) {
  like.addEventListener('click', liker);
}); // Post double-click like

document.querySelectorAll('.md-post').forEach(function (post) {
  var postId = post.id.replace('postId', '');
  post.querySelector('.post-image').addEventListener('dblclick', function (event) {
    var postLiker = post.querySelector('.like-toggle');
    var url = postLiker.href;
    axios.get(url).then(function (response) {
      var status = String(response.data.response.status);
      status === 'added' ? postLiker.classList.add('added') : postLiker.classList.remove('added');
      var postLikeCounter = 'post-like-counter-' + postLiker.id.replace('post-like-', '');
      var currentLikes = parseInt(document.getElementById(postLikeCounter).innerHTML);

      if (status === 'added') {
        post.querySelector('.post-liker').classList.add('like');
        setTimeout(function () {
          post.querySelector('.post-liker').classList.remove('like');
        }, 1000);
        document.getElementById(postLikeCounter).innerHTML = currentLikes + 1;
      } else {
        post.querySelector('.post-liker').classList.add('dislike');
        setTimeout(function () {
          post.querySelector('.post-liker').classList.remove('dislike');
        }, 1000);
        document.getElementById(postLikeCounter).innerHTML = currentLikes - 1;
      }

      setTimeout(function () {
        [postLiker].forEach(function (switcher) {
          switcher.style.pointerEvents = 'auto';
        });
      }, 100);
    });
  });
});

/***/ }),

/***/ "./assets/js/app6.js":
/*!***************************!*\
  !*** ./assets/js/app6.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _scss_app6_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../scss/app6.scss */ "./assets/scss/app6.scss");
/* harmony import */ var _scripts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./scripts */ "./assets/js/scripts.js");
/* harmony import */ var _scripts__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_scripts__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _ajax__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ajax */ "./assets/js/ajax.js");
/* harmony import */ var _ajax__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_ajax__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _share__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./share */ "./assets/js/share.js");
/* harmony import */ var _share__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_share__WEBPACK_IMPORTED_MODULE_3__);
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you import will output into a single scss file (appFile.scss in this case)
 // Awesome fonts

__webpack_require__(/*! @fortawesome/fontawesome-free/css/all.min.css */ "./node_modules/@fortawesome/fontawesome-free/css/all.min.css");

__webpack_require__(/*! @fortawesome/fontawesome-free/js/all.js */ "./node_modules/@fortawesome/fontawesome-free/js/all.js"); // Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';


var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

__webpack_require__.g.$ = __webpack_require__.g.jQuery = $; // Bootstrap js

__webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.js"); // My scripts






/***/ }),

/***/ "./assets/js/scripts.js":
/*!******************************!*\
  !*** ./assets/js/scripts.js ***!
  \******************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! core-js/modules/es.object.define-property.js */ "./node_modules/core-js/modules/es.object.define-property.js");

__webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");

__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");

__webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");

__webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");

__webpack_require__(/*! core-js/modules/es.array.iterator.js */ "./node_modules/core-js/modules/es.array.iterator.js");

__webpack_require__(/*! core-js/modules/es.string.iterator.js */ "./node_modules/core-js/modules/es.string.iterator.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");

__webpack_require__(/*! core-js/modules/web.url.js */ "./node_modules/core-js/modules/web.url.js");

__webpack_require__(/*! core-js/modules/web.url-search-params.js */ "./node_modules/core-js/modules/web.url-search-params.js");

__webpack_require__(/*! core-js/modules/es.regexp.constructor.js */ "./node_modules/core-js/modules/es.regexp.constructor.js");

__webpack_require__(/*! core-js/modules/es.regexp.to-string.js */ "./node_modules/core-js/modules/es.regexp.to-string.js");

// Prevent scroll
var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

var keys = {
  37: 1,
  38: 1,
  39: 1,
  40: 1
};

function preventDefault(e) {
  e.preventDefault();
}

function preventDefaultForScrollKeys(e) {
  if (keys[e.keyCode]) {
    preventDefault(e);
    return false;
  }
}

var supportsPassive = false;

try {
  window.addEventListener("test", null, Object.defineProperty({}, 'passive', {
    get: function get() {
      supportsPassive = true;
    }
  }));
} catch (e) {}

var wheelOpt = supportsPassive ? {
  passive: false
} : false;
var wheelEvent = 'onwheel' in document.createElement('div') ? 'wheel' : 'mousewheel';

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
} // Mobile navbar


var sideNav = document.getElementById('sideNav');
var sideNavOpener = document.getElementById('sideNavOpener');
var sideNavCloser = document.getElementById('sideNavCloser');
var sideNavLogoutCloser = document.getElementById('sideNavLogoutCloser');
var sideNavBack = document.getElementById('sideNavBack');

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
} // MediaElement Player


__webpack_require__(/*! mediaelement/build/mediaelement-and-player.min */ "./node_modules/mediaelement/build/mediaelement-and-player.min.js");

$(document).ready(function () {
  $('.audio-player audio').mediaelementplayer({
    success: function success(player, node) {
      // Optional
      $(player).closest('.mejs__container').attr('lang', mejs.i18n.language());
      $('html').attr('lang', mejs.i18n.language()); // More code
    },
    startVolume: 1,
    autoRewind: true,
    enableProgressTooltip: false,
    features: ['playpause', '[feature_name]', 'current', 'progress', 'duration']
  });
}); // Comment reply

var commentWrite = document.querySelector('.md-comment-write');
var commentTextArea = document.getElementById('comment_message');
var commentReply = document.querySelectorAll('.comment-reply');
var replyTo = document.getElementById('comment_replyTo');
var replyFor = document.getElementById('comment_replyFor');
var commentReplyUser = document.querySelector('.md-comment-reply-user');
var replyingDelete = document.querySelector('.md-replying-delete');

if (replyingDelete) {
  replyingDelete.addEventListener('click', function () {
    replyTo.removeAttribute('value');
    replyFor.removeAttribute('value');
    commentWrite.classList.remove('md-replying');
  });
}

if (commentReply) {
  commentReply.forEach(function (reply) {
    var replyUser = reply.querySelector('.reply-user');
    var replyComment = reply.querySelector('.reply-comment');
    reply.addEventListener('click', function (reply) {
      replyTo.value = replyUser.innerHTML;
      replyFor.value = replyComment.innerHTML;
      commentTextArea.focus();

      if (replyTo.value !== '') {
        commentWrite.classList.add('md-replying');
        commentReplyUser.innerHTML = replyTo.value;
      }
    });
  });
} // Searcher


var searchInputs = document.querySelectorAll('.md-search-all-input');
var navbarSearch = document.getElementById('navbarSearch'); // const navbarSearchInput = document.getElementById('navbarSearchInput');

var navbarSearchOpener = document.getElementById('openNavbarSearch');
var navbarSearchCloser = document.getElementById('closeNavbarSearch');

function openNavbarSearch() {
  navbarSearchOpener.style.cssText = 'display:none !important';
  document.querySelector('.navbar-search-mobile').style.display = 'unset';
  document.querySelector('.navbar-search-mobile input').focus();
}

function closeNavbarSearch() {
  document.querySelector('.navbar-search-mobile').style.display = 'none';
  navbarSearchOpener.style.display = 'none';
}

if (navbarSearch) {
  navbarSearchOpener.addEventListener('click', openNavbarSearch);
  navbarSearchCloser.addEventListener('click', closeNavbarSearch);
  document.querySelector('.body-wrapper').addEventListener('click', closeNavbarSearch);
}

searchInputs.forEach(function (inputBox, key) {
  inputBox.querySelector('.search_input').addEventListener('input', function (input) {
    inputBox.querySelector('.search_button').href = '/search/' + input.target.value.replace('#', '%23').replace('%', '%25');
  });
  inputBox.querySelector('.search_input').addEventListener('keyup', function (event) {
    if (event.keyCode === 13 || event.code === "Enter") {
      inputBox.querySelector('.search_button').click();
    }
  });
}); // Enable tooltip

$(function () {
  $('[data-toggle="tooltip"]').tooltip({
    trigger: 'hover'
  });
}); // Image on change

if (document.querySelector('.custom-file-input')) {
  var fileInput = document.querySelector('.custom-file-input');

  if (document.getElementById('postImgOutput')) {
    var postImgOutput = document.getElementById('postImgOutput');

    fileInput.onchange = function () {
      postImgOutput.src = window.URL.createObjectURL(fileInput.files[0]);
      postImgOutput.style.marginBottom = '8px';
    };
  } else if (document.getElementById('output-content')) {
    var outputContent = document.getElementById('output-content');

    fileInput.onchange = function () {
      outputContent.style.backgroundImage = 'url(\'' + window.URL.createObjectURL(fileInput.files[0]) + '\')';
    };
  }
} // Textarea autosize


if (document.querySelector('.md-auto-sizer')) {
  var textarea = document.querySelector('.md-auto-sizer');

  textarea.oninput = function () {
    textarea.style.height = textarea.scrollHeight + 2 + "px";
  };
} // Auto close alerts


$(".md-alert-auto-hide").fadeTo(5000, 500).slideUp(500, function () {
  $(".md-alert-auto-hide").slideUp(500);
}); // Prevent username symbols

$('.username-input').on('keypress', function (event) {
  var regex = new RegExp("^[a-zA-Z0-9._]+$");
  var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);

  if (!regex.test(key)) {
    event.preventDefault();
    return false;
  }
}); // Post collapse

var postCollapse = document.querySelectorAll('.post-collapse');
postCollapse.forEach(function (post) {
  function openCollapsedPost() {
    this.style.display = 'none';
    document.getElementById('postCollapse' + postId).classList.remove('closed');
  }

  var postId = post.id.replace('postCollapse', '');

  if (post.querySelector('div').clientHeight > 50) {
    document.getElementById('postCollapseButton' + postId).style.display = 'block';
  }

  document.getElementById('postCollapseButton' + postId).addEventListener('click', openCollapsedPost);
}); // Modal change body color
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

/***/ }),

/***/ "./assets/js/share.js":
/*!****************************!*\
  !*** ./assets/js/share.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
__webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");

__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");

__webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");

__webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");

var axios = (__webpack_require__(/*! axios */ "./node_modules/axios/index.js")["default"]); // -------------------- Share global functions -------------------- //


function shareMediaAlert(event, user, type) {
  function shareType(type) {
    if (type === 'Profile') {
      return user.querySelector('.md-user-id').innerHTML;
    } else {
      return user.querySelector('.md-username').innerHTML;
    }
  }

  var username = shareType(type);
  var media = document.getElementById('share' + type + 'Id').innerHTML;
  var url = '/share' + type + '/' + media + '/' + username;
  document.getElementById('share' + type + 'Username').innerHTML = user.querySelector('.md-username').innerHTML;
  document.getElementById('share' + type + 'Alert').querySelector('a').href = url;
  document.getElementById('share' + type + 'Modal').click();
  document.getElementById('share' + type + 'Confirm').click();
}

function shareMedia(event, type) {
  event.preventDefault();
  var url = event.target.href;
  axios.get(url).then(function (response) {
    document.getElementById('share' + type + 'Alert').click();
    var message = '<div class="md-alert md-alert-success md-box-mb">' + response.data.response.message + '</div>';
    var alertBox = document.querySelector('main');
    var alertExist = document.querySelector('.md-alert');

    if (alertExist) {
      alertExist.outerHTML = message;
    } else {
      alertBox.insertAdjacentHTML('afterbegin', message);
    }

    $(".md-alert").fadeTo(3000, 500).slideUp(500, function () {
      $(".md-alert").slideUp(500);
    });
  });
} // -------------------- Share song -------------------- //
// Song share alert


var shareSongUsers = document.querySelectorAll('#shareSongModal .user-line');
shareSongUsers.forEach(function (user) {
  user.addEventListener('click', function (event) {
    shareMediaAlert(event, user, 'Song');
  });
}); // If song share confirm

var shareSongButton = document.getElementById('shareSongConfirmButton');

if (shareSongButton) {
  shareSongButton.addEventListener('click', function (event) {
    shareMedia(event, 'Song');
  });
} // If song share cancel


function shareSongCancelFunction(event) {
  event.preventDefault();
  document.getElementById('shareSongAlert').click();
  document.getElementById('shareSong').click();
}

var shareSongCancel = document.getElementById('shareSongCancel');

if (shareSongCancel) {
  shareSongCancel.addEventListener('click', shareSongCancelFunction);
} // -------------------- Share post -------------------- //
// Insert post Id


function sharePostButtonFunction() {
  document.getElementById('sharePostId').innerHTML = this.id.replace('sharePostId', '');
}

var sharePostButton = document.querySelectorAll('.share-post-button');
sharePostButton.forEach(function (sharePostButton) {
  sharePostButton.addEventListener('click', sharePostButtonFunction);
}); // Post share alert

var sharePostUsers = document.querySelectorAll('#sharePostModal .user-line');
sharePostUsers.forEach(function (user) {
  user.addEventListener('click', function (event) {
    shareMediaAlert(event, user, 'Post');
  });
}); // If post share confirm

var sharePostConfirmButton = document.getElementById('sharePostConfirmButton');

if (sharePostConfirmButton) {
  sharePostConfirmButton.addEventListener('click', function (event) {
    shareMedia(event, 'Post');
  });
} // If post share cancel


function sharePostCancelFunction() {
  document.getElementById('sharePostAlert').click();
  document.getElementById('sharePostId' + document.getElementById('sharePostId').innerHTML).click();
}

var sharePostCancel = document.getElementById('sharePostCancel');

if (sharePostCancel) {
  sharePostCancel.addEventListener('click', sharePostCancelFunction);
} // -------------------- Share profile -------------------- //
// Profile share alert


var shareProfileUsers = document.querySelectorAll('#shareProfileModal .user-line');
shareProfileUsers.forEach(function (user) {
  user.addEventListener('click', function (event) {
    shareMediaAlert(event, user, 'Profile');
  });
}); // If profile share confirm

var shareProfileButton = document.getElementById('shareProfileConfirmButton');

if (shareProfileButton) {
  shareProfileButton.addEventListener('click', function (event) {
    shareMedia(event, 'Profile');
  });
} // If profile share cancel


function shareProfileCancelFunction(event) {
  event.preventDefault();
  document.getElementById('shareProfileAlert').click();
  document.getElementById('shareProfile').click();
}

var shareProfileCancel = document.getElementById('shareProfileCancel');

if (shareProfileCancel) {
  shareProfileCancel.addEventListener('click', shareProfileCancelFunction);
}

/***/ }),

/***/ "./assets/scss/app6.scss":
/*!*******************************!*\
  !*** ./assets/scss/app6.scss ***!
  \*******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_fortawesome_fontawesome-free_js_all_js-node_modules_fortawesome_fontawes-8625a3"], () => (__webpack_exec__("./assets/js/app6.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QUFULENBQXdCLG9CQUF4QixDQUF6Qjs7QUFDQSxJQUFJbUosa0JBQUosRUFBd0I7QUFDcEJBLEVBQUFBLGtCQUFrQixDQUFDdEosZ0JBQW5CLENBQW9DLE9BQXBDLEVBQTZDcUosMEJBQTdDO0FBQ0g7Ozs7Ozs7Ozs7OztBQ25KRCIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Fzc2V0cy9qcy9hamF4LmpzIiwid2VicGFjazovLy8uL2Fzc2V0cy9qcy9hcHA2LmpzIiwid2VicGFjazovLy8uL2Fzc2V0cy9qcy9zY3JpcHRzLmpzIiwid2VicGFjazovLy8uL2Fzc2V0cy9qcy9zaGFyZS5qcyIsIndlYnBhY2s6Ly8vLi9hc3NldHMvc2Nzcy9hcHA2LnNjc3MiXSwic291cmNlc0NvbnRlbnQiOlsiY29uc3QgYXhpb3MgPSByZXF1aXJlKCdheGlvcycpLmRlZmF1bHQ7XG5cbi8vIEFkZCBzb25nIHRvIHBsYXlsaXN0IG9yIGFkZCBwb3N0IHRvIGJvb2ttYXJrc1xuXG5sZXQgcGxheWxpc3QgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcucGxheWxpc3QtdG9nZ2xlJyk7XG5sZXQgYm9va21hcmsgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcuYm9va21hcmstdG9nZ2xlJyk7XG5cbmZ1bmN0aW9uIHN3aXRjaGVyKGV2ZW50KSB7XG4gICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICBsZXQgdXJsID0gdGhpcy5ocmVmO1xuXG4gICAgYXhpb3MuZ2V0KHVybCkudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgbGV0IHN0YXR1cyA9IFN0cmluZyhyZXNwb25zZS5kYXRhLnJlc3BvbnNlLnN0YXR1cyk7XG4gICAgICAgIChzdGF0dXMgPT09ICdhZGRlZCcpID8gdGhpcy5jbGFzc0xpc3QuYWRkKCdhZGRlZCcpIDogdGhpcy5jbGFzc0xpc3QucmVtb3ZlKCdhZGRlZCcpO1xuICAgICAgICB0aGlzLmRhdGFzZXQub3JpZ2luYWxUaXRsZSA9IHJlc3BvbnNlLmRhdGEucmVzcG9uc2UudGl0bGU7XG4gICAgICAgIHRoaXMuc3R5bGUucG9pbnRlckV2ZW50cyA9ICdub25lJztcblxuICAgICAgICBsZXQgYWxlcnRCb3ggPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdtYWluJyk7XG4gICAgICAgIGxldCBhbGVydEV4aXN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLm1kLWFsZXJ0Jyk7XG5cbiAgICAgICAgaWYgKHJlc3BvbnNlLmRhdGEucmVzcG9uc2UubWVzc2FnZSkge1xuICAgICAgICAgICAgbGV0IGJhZGdlID0gJyc7XG4gICAgICAgICAgICBpZiAocmVzcG9uc2UuZGF0YS5yZXNwb25zZS5zdGF0dXMgPT09ICdhZGRlZCcpIHtcbiAgICAgICAgICAgICAgICBiYWRnZSA9ICdzdWNjZXNzJztcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgYmFkZ2UgPSAnZGFuZ2VyJztcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgbGV0IG1lc3NhZ2UgPSAnPGRpdiBjbGFzcz1cIm1kLWFsZXJ0IG1kLWFsZXJ0LScgKyBiYWRnZSArICcgbWQtYm94LW1iXCI+JyArXG4gICAgICAgICAgICAgICAgcmVzcG9uc2UuZGF0YS5yZXNwb25zZS5tZXNzYWdlICtcbiAgICAgICAgICAgICAgICAnPC9kaXY+JztcblxuICAgICAgICAgICAgaWYgKGFsZXJ0RXhpc3QpIHtcbiAgICAgICAgICAgICAgICBhbGVydEV4aXN0Lm91dGVySFRNTCA9IG1lc3NhZ2U7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGFsZXJ0Qm94Lmluc2VydEFkamFjZW50SFRNTCgnYWZ0ZXJiZWdpbicsIG1lc3NhZ2UpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAkKFwiLm1kLWFsZXJ0XCIpLmZhZGVUbygzMDAwLCA1MDApLnNsaWRlVXAoNTAwLCBmdW5jdGlvbigpe1xuICAgICAgICAgICAgICAgICQoXCIubWQtYWxlcnRcIikuc2xpZGVVcCg1MDApO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cblxuICAgICAgICBzZXRUaW1lb3V0KCgpID0+IHtcbiAgICAgICAgICAgIFt0aGlzXS5mb3JFYWNoKChzd2l0Y2hlcikgPT4ge1xuICAgICAgICAgICAgICAgIHN3aXRjaGVyLnN0eWxlLnBvaW50ZXJFdmVudHMgPSAnYXV0byc7XG4gICAgICAgICAgICB9KVxuICAgICAgICB9LCAxMDApO1xuICAgIH0pXG59XG5cbnBsYXlsaXN0LmZvckVhY2goKHBsYXlsaXN0KSA9PiB7XG4gICAgcGxheWxpc3QuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBzd2l0Y2hlcik7XG59KTtcblxuYm9va21hcmsuZm9yRWFjaCgoYm9va21hcmspID0+IHtcbiAgICBib29rbWFyay5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIHN3aXRjaGVyKTtcbn0pO1xuXG4vLyBGb2xsb3cgYSB1c2VyIG9yIHVuZm9sbG93IGZyb20geW91cnNlbGZcblxubGV0IGZvbGxvdyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy5mb2xsb3ctdG9nZ2xlJyk7XG5cbmZ1bmN0aW9uIGZvbGxvd3MoZXZlbnQpIHtcbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIGxldCB1cmwgPSB0aGlzLmhyZWY7XG5cbiAgICBheGlvcy5nZXQodXJsKS50aGVuKChyZXNwb25zZSkgPT4ge1xuICAgICAgICBsZXQgc3RhdHVzID0gU3RyaW5nKHJlc3BvbnNlLmRhdGEucmVzcG9uc2Uuc3RhdHVzKTtcblxuICAgICAgICBpZiAoc3RhdHVzID09PSAnYWRkZWQnKSB7XG4gICAgICAgICAgICB0aGlzLmNsYXNzTGlzdC5yZW1vdmUoJ2J0bi1pbmZvJyk7XG4gICAgICAgICAgICB0aGlzLmNsYXNzTGlzdC5hZGQoJ2J0bi1saWdodCcpO1xuICAgICAgICAgICAgdGhpcy5jbGFzc0xpc3QuYWRkKCdmb2xsb3dlZCcpO1xuXG4gICAgICAgICAgICBpZiAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Byb2ZpbGVGb2xsb3dlcnMnKSkge1xuICAgICAgICAgICAgICAgIGxldCBmb2xsb3dlcnMgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncHJvZmlsZUZvbGxvd2VycycpLnF1ZXJ5U2VsZWN0b3IoJy5udW1iZXInKS5pbm5lckhUTUw7XG4gICAgICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Byb2ZpbGVGb2xsb3dlcnMnKS5xdWVyeVNlbGVjdG9yKCcubnVtYmVyJykuaW5uZXJIVE1MID0gKHBhcnNlSW50KGZvbGxvd2VycywgMTApICsgMSkudG9TdHJpbmcoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIGlmIChzdGF0dXMgPT09ICdyZXF1ZXN0ZWQnKSB7XG4gICAgICAgICAgICB0aGlzLmNsYXNzTGlzdC5yZW1vdmUoJ2J0bi1pbmZvJyk7XG4gICAgICAgICAgICB0aGlzLmNsYXNzTGlzdC5hZGQoJ2J0bi1saWdodCcpO1xuICAgICAgICAgICAgdGhpcy5jbGFzc0xpc3QuYWRkKCdyZXF1ZXN0ZWQnKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGlmIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncHJvZmlsZUZvbGxvd2VycycpICYmICFkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcucmVxdWVzdGVkJykpIHtcbiAgICAgICAgICAgICAgICBsZXQgZm9sbG93ZXJzID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Byb2ZpbGVGb2xsb3dlcnMnKS5xdWVyeVNlbGVjdG9yKCcubnVtYmVyJykuaW5uZXJIVE1MO1xuICAgICAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdwcm9maWxlRm9sbG93ZXJzJykucXVlcnlTZWxlY3RvcignLm51bWJlcicpLmlubmVySFRNTCA9IChwYXJzZUludChmb2xsb3dlcnMsIDEwKSAtIDEpLnRvU3RyaW5nKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB0aGlzLmNsYXNzTGlzdC5yZW1vdmUoJ2J0bi1saWdodCcpO1xuICAgICAgICAgICAgdGhpcy5jbGFzc0xpc3QucmVtb3ZlKCdmb2xsb3dlZCcpO1xuICAgICAgICAgICAgdGhpcy5jbGFzc0xpc3QucmVtb3ZlKCdyZXF1ZXN0ZWQnKTtcbiAgICAgICAgICAgIHRoaXMuY2xhc3NMaXN0LmFkZCgnYnRuLWluZm8nKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHRoaXMuZGF0YXNldC5vcmlnaW5hbFRpdGxlID0gcmVzcG9uc2UuZGF0YS5yZXNwb25zZS50aXRsZTtcbiAgICAgICAgdGhpcy5zdHlsZS5wb2ludGVyRXZlbnRzID0gJ25vbmUnO1xuXG4gICAgICAgIHNldFRpbWVvdXQoKCkgPT4ge1xuICAgICAgICAgICAgW3RoaXNdLmZvckVhY2goKHN3aXRjaGVyKSA9PiB7XG4gICAgICAgICAgICAgICAgc3dpdGNoZXIuc3R5bGUucG9pbnRlckV2ZW50cyA9ICdhdXRvJztcbiAgICAgICAgICAgIH0pXG4gICAgICAgIH0sIDEwMCk7XG4gICAgfSlcbn1cblxubGV0IHVuZm9sbG93ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLnVuZm9sbG93LXRvZ2dsZScpO1xuXG5mb2xsb3cuZm9yRWFjaCgoZm9sbG93KSA9PiB7XG4gICAgZm9sbG93LmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZm9sbG93cyk7XG59KTtcblxuZnVuY3Rpb24gdW5mb2xsb3dzKGV2ZW50KSB7XG4gICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICBsZXQgdXJsID0gdGhpcy5ocmVmO1xuICAgIGxldCBmb2xsb3dlckJsb2NrID0gJ3UnICsgdGhpcy5pZC5zcGxpdCgndScpLnBvcCgpLnNwbGl0KCd0JylbMF0gKyAnbCc7XG4gICAgbGV0IGNhbmNlbEJ1dHRvbklkID0gJ3VzZXJVbmZvbGxvdycgKyB0aGlzLmlkLnNwbGl0KCd1JykucG9wKCkuc3BsaXQoJ3QnKVswXTtcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChjYW5jZWxCdXR0b25JZCkuY2xpY2soKTtcblxuICAgIGF4aW9zLmdldCh1cmwpLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgIGxldCBzdGF0dXMgPSBTdHJpbmcocmVzcG9uc2UuZGF0YS5yZXNwb25zZS5zdGF0dXMpO1xuICAgICAgICBpZiAoc3RhdHVzID09PSAncmVtb3ZlZCcpIHtcbiAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGZvbGxvd2VyQmxvY2spLnJlbW92ZSgpO1xuICAgICAgICB9XG4gICAgfSlcbn1cblxudW5mb2xsb3cuZm9yRWFjaCgodW5mb2xsb3cpID0+IHtcbiAgICB1bmZvbGxvdy5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIHVuZm9sbG93cyk7XG59KTtcblxuLy8gQWNjZXB0IG9yIHJlamVjdCBmb2xsb3cgcmVxdWVzdFxuXG5mdW5jdGlvbiByZWplY3RSZXF1ZXN0RnVuY3Rpb24oZXZlbnQsIHJlcXVlc3RSZXNwb25zZSkge1xuICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgbGV0IHVybCA9IGV2ZW50LnRhcmdldC5ocmVmO1xuICAgIGxldCBmb2xsb3dlckJsb2NrID0gJ3UnICsgZXZlbnQudGFyZ2V0LmlkLnNwbGl0KCd1JykucG9wKCkuc3BsaXQoJ3QnKVswXSArICdsJztcblxuICAgIGlmIChyZXF1ZXN0UmVzcG9uc2UgPT09ICdyZWplY3RlZCcpIHtcbiAgICAgICAgbGV0IGNhbmNlbEJ1dHRvbklkID0gJ3JlamVjdFJlcXVlc3QnICsgZXZlbnQudGFyZ2V0LmlkLnNwbGl0KCd1JykucG9wKCkuc3BsaXQoJ3QnKVswXTtcbiAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoY2FuY2VsQnV0dG9uSWQpLmNsaWNrKCk7XG4gICAgfVxuXG4gICAgYXhpb3MuZ2V0KHVybCkudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgbGV0IHJlcXVlc3QgPSBTdHJpbmcocmVzcG9uc2UuZGF0YS5yZXNwb25zZS5yZXF1ZXN0KTtcbiAgICAgICAgaWYgKHJlcXVlc3QgPT09IHJlcXVlc3RSZXNwb25zZSkge1xuICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoZm9sbG93ZXJCbG9jaykucmVtb3ZlKCk7XG4gICAgICAgICAgICBpZiAoZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLnVzZXItbGluZScpID09PSBudWxsKSB7XG4gICAgICAgICAgICAgICAgaWYgKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZXF1ZXN0c1BhZ2luYXRvcicpKSB7XG4gICAgICAgICAgICAgICAgICAgIGRvY3VtZW50LmxvY2F0aW9uLnJlbG9hZCgpO1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZXF1ZXN0c0xpc3QnKS5yZW1vdmUoKTtcbiAgICAgICAgICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3JlcXVlc3RzSXNFbXB0eScpLnN0eWxlLmRpc3BsYXkgPSAnYmxvY2snO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0pXG59XG5cbmxldCBhY2NlcHRSZXF1ZXN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLmFjY2VwdC1mb2xsb3ctcmVxdWVzdCcpO1xubGV0IHJlamVjdFJlcXVlc3QgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcucmVqZWN0LXJlcXVlc3QtdG9nZ2xlJyk7XG5cbmFjY2VwdFJlcXVlc3QuZm9yRWFjaCgoYWNjZXB0UmVxdWVzdCkgPT4ge1xuICAgIGFjY2VwdFJlcXVlc3QuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoZXZlbnQpID0+IHtcbiAgICAgICAgcmVqZWN0UmVxdWVzdEZ1bmN0aW9uKGV2ZW50LCdhY2NlcHRlZCcpXG4gICAgfSk7XG59KTtcblxucmVqZWN0UmVxdWVzdC5mb3JFYWNoKChyZWplY3RSZXF1ZXN0KSA9PiB7XG4gICAgcmVqZWN0UmVxdWVzdC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIChldmVudCkgPT4ge1xuICAgICAgICByZWplY3RSZXF1ZXN0RnVuY3Rpb24oZXZlbnQsJ3JlamVjdGVkJylcbiAgICB9KTtcbn0pO1xuXG4vLyBNYWtlIHBvc3QgZmVhdHVyZWRcblxubGV0IGZlYXR1cmVkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLmZlYXR1cmVkLXRvZ2dsZScpO1xuXG5mdW5jdGlvbiBmZWF0dXJlZFBvc3QoZXZlbnQpIHtcbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIGxldCB1cmwgPSB0aGlzLmhyZWY7XG5cbiAgICBheGlvcy5nZXQodXJsKS50aGVuKChyZXNwb25zZSkgPT4ge1xuICAgICAgICBsZXQgc3RhdHVzID0gU3RyaW5nKHJlc3BvbnNlLmRhdGEucmVzcG9uc2Uuc3RhdHVzKTtcbiAgICAgICAgKHN0YXR1cyA9PT0gJ2FkZGVkJykgPyB0aGlzLmNsYXNzTGlzdC5hZGQoJ2FkZGVkJykgOiB0aGlzLmNsYXNzTGlzdC5yZW1vdmUoJ2FkZGVkJyk7XG5cbiAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICBbdGhpc10uZm9yRWFjaCgoc3dpdGNoZXIpID0+IHtcbiAgICAgICAgICAgICAgICBzd2l0Y2hlci5zdHlsZS5wb2ludGVyRXZlbnRzID0gJ2F1dG8nO1xuICAgICAgICAgICAgfSlcbiAgICAgICAgfSwgMTAwKTtcbiAgICB9KVxufVxuXG5mZWF0dXJlZC5mb3JFYWNoKChmZWF0dXJlZCkgPT4ge1xuICAgIGZlYXR1cmVkLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgZmVhdHVyZWRQb3N0KTtcbn0pO1xuXG4vLyBMaWtlc1xuXG5sZXQgbGlrZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy5saWtlLXRvZ2dsZScpO1xuXG5mdW5jdGlvbiBsaWtlcihldmVudCkge1xuICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgbGV0IHVybCA9IHRoaXMuaHJlZjtcblxuICAgIGF4aW9zLmdldCh1cmwpLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgIGxldCBzdGF0dXMgPSBTdHJpbmcocmVzcG9uc2UuZGF0YS5yZXNwb25zZS5zdGF0dXMpO1xuICAgICAgICAoc3RhdHVzID09PSAnYWRkZWQnKSA/IHRoaXMuY2xhc3NMaXN0LmFkZCgnYWRkZWQnKSA6IHRoaXMuY2xhc3NMaXN0LnJlbW92ZSgnYWRkZWQnKTtcblxuICAgICAgICBmdW5jdGlvbiBjb250YWlucyh2YWx1ZSkge1xuICAgICAgICAgICAgcmV0dXJuIHZhbHVlLmluY2x1ZGVzKCdwb3N0JykgPyBcInBvc3RcIiA6IFwiY29tbWVudFwiO1xuICAgICAgICB9XG5cbiAgICAgICAgbGV0IGxpa2VDb3VudGVyID0gY29udGFpbnModGhpcy5pZCkgKyAnLWxpa2UtY291bnRlci0nICsgdGhpcy5pZC5yZXBsYWNlKGNvbnRhaW5zKHRoaXMuaWQpICsgJy1saWtlLScsJycpO1xuICAgICAgICBsZXQgY3VycmVudExpa2VzID0gcGFyc2VJbnQoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQobGlrZUNvdW50ZXIpLmlubmVySFRNTCk7XG5cbiAgICAgICAgaWYgKHN0YXR1cyA9PT0gJ2FkZGVkJykge1xuICAgICAgICAgICAgaWYgKGNvbnRhaW5zKHRoaXMuaWQpID09PSAnY29tbWVudCcpIHtcbiAgICAgICAgICAgICAgICBpZiAoY3VycmVudExpa2VzID09PSAwKSB7XG4gICAgICAgICAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGxpa2VDb3VudGVyKS5wYXJlbnRFbGVtZW50LnN0eWxlLmRpc3BsYXkgPSAndW5zZXQnO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGxpa2VDb3VudGVyKS5pbm5lckhUTUwgPSBjdXJyZW50TGlrZXMgKyAxO1xuICAgICAgICB9IGVsc2UgIHtcbiAgICAgICAgICAgIGlmIChjb250YWlucyh0aGlzLmlkKSA9PT0gJ2NvbW1lbnQnKSB7XG4gICAgICAgICAgICAgICAgaWYgKGN1cnJlbnRMaWtlcyA9PT0gMSkge1xuICAgICAgICAgICAgICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChsaWtlQ291bnRlcikucGFyZW50RWxlbWVudC5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGxpa2VDb3VudGVyKS5pbm5lckhUTUwgPSBjdXJyZW50TGlrZXMgLSAxO1xuICAgICAgICB9XG5cbiAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICBbdGhpc10uZm9yRWFjaCgoc3dpdGNoZXIpID0+IHtcbiAgICAgICAgICAgICAgICBzd2l0Y2hlci5zdHlsZS5wb2ludGVyRXZlbnRzID0gJ2F1dG8nO1xuICAgICAgICAgICAgfSlcbiAgICAgICAgfSwgMTAwKTtcbiAgICB9KVxufVxuXG5saWtlLmZvckVhY2goKGxpa2UpID0+IHtcbiAgICBsaWtlLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgbGlrZXIpO1xufSk7XG5cbi8vIFBvc3QgZG91YmxlLWNsaWNrIGxpa2VcblxuZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLm1kLXBvc3QnKS5mb3JFYWNoKChwb3N0KSA9PiB7XG4gICAgbGV0IHBvc3RJZCA9IHBvc3QuaWQucmVwbGFjZSgncG9zdElkJywgJycpO1xuICAgIHBvc3QucXVlcnlTZWxlY3RvcignLnBvc3QtaW1hZ2UnKS5hZGRFdmVudExpc3RlbmVyKCdkYmxjbGljaycsKGV2ZW50KSA9PiB7XG4gICAgICAgIGxldCBwb3N0TGlrZXIgPSBwb3N0LnF1ZXJ5U2VsZWN0b3IoJy5saWtlLXRvZ2dsZScpO1xuICAgICAgICBsZXQgdXJsID0gcG9zdExpa2VyLmhyZWY7XG5cbiAgICAgICAgYXhpb3MuZ2V0KHVybCkudGhlbigocmVzcG9uc2UpID0+IHtcbiAgICAgICAgICAgIGxldCBzdGF0dXMgPSBTdHJpbmcocmVzcG9uc2UuZGF0YS5yZXNwb25zZS5zdGF0dXMpO1xuICAgICAgICAgICAgKHN0YXR1cyA9PT0gJ2FkZGVkJykgPyBwb3N0TGlrZXIuY2xhc3NMaXN0LmFkZCgnYWRkZWQnKSA6IHBvc3RMaWtlci5jbGFzc0xpc3QucmVtb3ZlKCdhZGRlZCcpO1xuXG4gICAgICAgICAgICBsZXQgcG9zdExpa2VDb3VudGVyID0gJ3Bvc3QtbGlrZS1jb3VudGVyLScgKyBwb3N0TGlrZXIuaWQucmVwbGFjZSgncG9zdC1saWtlLScsJycpO1xuICAgICAgICAgICAgbGV0IGN1cnJlbnRMaWtlcyA9IHBhcnNlSW50KGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKHBvc3RMaWtlQ291bnRlcikuaW5uZXJIVE1MKTtcblxuICAgICAgICAgICAgaWYgKHN0YXR1cyA9PT0gJ2FkZGVkJykge1xuICAgICAgICAgICAgICAgIHBvc3QucXVlcnlTZWxlY3RvcignLnBvc3QtbGlrZXInKS5jbGFzc0xpc3QuYWRkKCdsaWtlJyk7XG4gICAgICAgICAgICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHBvc3QucXVlcnlTZWxlY3RvcignLnBvc3QtbGlrZXInKS5jbGFzc0xpc3QucmVtb3ZlKCdsaWtlJyk7XG4gICAgICAgICAgICAgICAgfSwgMTAwMCk7XG4gICAgICAgICAgICAgICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQocG9zdExpa2VDb3VudGVyKS5pbm5lckhUTUwgPSBjdXJyZW50TGlrZXMgKyAxO1xuICAgICAgICAgICAgfSBlbHNlICB7XG4gICAgICAgICAgICAgICAgcG9zdC5xdWVyeVNlbGVjdG9yKCcucG9zdC1saWtlcicpLmNsYXNzTGlzdC5hZGQoJ2Rpc2xpa2UnKTtcbiAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KCgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgcG9zdC5xdWVyeVNlbGVjdG9yKCcucG9zdC1saWtlcicpLmNsYXNzTGlzdC5yZW1vdmUoJ2Rpc2xpa2UnKTtcbiAgICAgICAgICAgICAgICB9LCAxMDAwKTtcbiAgICAgICAgICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChwb3N0TGlrZUNvdW50ZXIpLmlubmVySFRNTCA9IGN1cnJlbnRMaWtlcyAtIDE7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHNldFRpbWVvdXQoKCkgPT4ge1xuICAgICAgICAgICAgICAgIFtwb3N0TGlrZXJdLmZvckVhY2goKHN3aXRjaGVyKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHN3aXRjaGVyLnN0eWxlLnBvaW50ZXJFdmVudHMgPSAnYXV0byc7XG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgIH0sIDEwMCk7XG4gICAgICAgIH0pXG4gICAgfSk7XG59KTtcbiIsIi8qXG4gKiBXZWxjb21lIHRvIHlvdXIgYXBwJ3MgbWFpbiBKYXZhU2NyaXB0IGZpbGUhXG4gKlxuICogV2UgcmVjb21tZW5kIGluY2x1ZGluZyB0aGUgYnVpbHQgdmVyc2lvbiBvZiB0aGlzIEphdmFTY3JpcHQgZmlsZVxuICogKGFuZCBpdHMgQ1NTIGZpbGUpIGluIHlvdXIgYmFzZSBsYXlvdXQgKGJhc2UuaHRtbC50d2lnKS5cbiAqL1xuXG4vLyBhbnkgQ1NTIHlvdSBpbXBvcnQgd2lsbCBvdXRwdXQgaW50byBhIHNpbmdsZSBzY3NzIGZpbGUgKGFwcEZpbGUuc2NzcyBpbiB0aGlzIGNhc2UpXG5pbXBvcnQgJy4uL3Njc3MvYXBwNi5zY3NzJztcblxuLy8gQXdlc29tZSBmb250c1xucmVxdWlyZSgnQGZvcnRhd2Vzb21lL2ZvbnRhd2Vzb21lLWZyZWUvY3NzL2FsbC5taW4uY3NzJyk7XG5yZXF1aXJlKCdAZm9ydGF3ZXNvbWUvZm9udGF3ZXNvbWUtZnJlZS9qcy9hbGwuanMnKTtcblxuLy8gTmVlZCBqUXVlcnk/IEluc3RhbGwgaXQgd2l0aCBcInlhcm4gYWRkIGpxdWVyeVwiLCB0aGVuIHVuY29tbWVudCB0byBpbXBvcnQgaXQuXG4vLyBpbXBvcnQgJCBmcm9tICdqcXVlcnknO1xuY29uc3QgJCA9IHJlcXVpcmUoJ2pxdWVyeScpO1xuZ2xvYmFsLiQgPSBnbG9iYWwualF1ZXJ5ID0gJDtcblxuLy8gQm9vdHN0cmFwIGpzXG5yZXF1aXJlKCdib290c3RyYXAnKTtcblxuLy8gTXkgc2NyaXB0c1xuaW1wb3J0ICcuL3NjcmlwdHMnO1xuaW1wb3J0ICcuL2FqYXgnO1xuaW1wb3J0ICcuL3NoYXJlJztcbiIsIi8vIFByZXZlbnQgc2Nyb2xsXG5cbmNvbnN0ICQgPSByZXF1aXJlKFwianF1ZXJ5XCIpO1xubGV0IGtleXMgPSB7Mzc6IDEsIDM4OiAxLCAzOTogMSwgNDA6IDF9O1xuXG5mdW5jdGlvbiBwcmV2ZW50RGVmYXVsdChlKSB7XG4gICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xufVxuXG5mdW5jdGlvbiBwcmV2ZW50RGVmYXVsdEZvclNjcm9sbEtleXMoZSkge1xuICAgIGlmIChrZXlzW2Uua2V5Q29kZV0pIHtcbiAgICAgICAgcHJldmVudERlZmF1bHQoZSk7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG59XG5cbmxldCBzdXBwb3J0c1Bhc3NpdmUgPSBmYWxzZTtcbnRyeSB7XG4gICAgd2luZG93LmFkZEV2ZW50TGlzdGVuZXIoXCJ0ZXN0XCIsIG51bGwsIE9iamVjdC5kZWZpbmVQcm9wZXJ0eSh7fSwgJ3Bhc3NpdmUnLCB7XG4gICAgICAgIGdldDogZnVuY3Rpb24gKCkgeyBzdXBwb3J0c1Bhc3NpdmUgPSB0cnVlOyB9XG4gICAgfSkpO1xufSBjYXRjaChlKSB7fVxuXG5sZXQgd2hlZWxPcHQgPSBzdXBwb3J0c1Bhc3NpdmUgPyB7IHBhc3NpdmU6IGZhbHNlIH0gOiBmYWxzZTtcbmxldCB3aGVlbEV2ZW50ID0gJ29ud2hlZWwnIGluIGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2RpdicpID8gJ3doZWVsJyA6ICdtb3VzZXdoZWVsJztcblxuZnVuY3Rpb24gZGlzYWJsZVNjcm9sbCgpIHtcbiAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcignRE9NTW91c2VTY3JvbGwnLCBwcmV2ZW50RGVmYXVsdCwgZmFsc2UpOyAvLyBvbGRlciBGRlxuICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKHdoZWVsRXZlbnQsIHByZXZlbnREZWZhdWx0LCB3aGVlbE9wdCk7IC8vIG1vZGVybiBkZXNrdG9wXG4gICAgd2luZG93LmFkZEV2ZW50TGlzdGVuZXIoJ3RvdWNobW92ZScsIHByZXZlbnREZWZhdWx0LCB3aGVlbE9wdCk7IC8vIG1vYmlsZVxuICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdrZXlkb3duJywgcHJldmVudERlZmF1bHRGb3JTY3JvbGxLZXlzLCBmYWxzZSk7XG59XG5cbmZ1bmN0aW9uIGVuYWJsZVNjcm9sbCgpIHtcbiAgICB3aW5kb3cucmVtb3ZlRXZlbnRMaXN0ZW5lcignRE9NTW91c2VTY3JvbGwnLCBwcmV2ZW50RGVmYXVsdCwgZmFsc2UpO1xuICAgIHdpbmRvdy5yZW1vdmVFdmVudExpc3RlbmVyKHdoZWVsRXZlbnQsIHByZXZlbnREZWZhdWx0LCB3aGVlbE9wdCk7XG4gICAgd2luZG93LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ3RvdWNobW92ZScsIHByZXZlbnREZWZhdWx0LCB3aGVlbE9wdCk7XG4gICAgd2luZG93LnJlbW92ZUV2ZW50TGlzdGVuZXIoJ2tleWRvd24nLCBwcmV2ZW50RGVmYXVsdEZvclNjcm9sbEtleXMsIGZhbHNlKTtcbn1cblxuLy8gTW9iaWxlIG5hdmJhclxuXG5jb25zdCBzaWRlTmF2ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NpZGVOYXYnKTtcbmNvbnN0IHNpZGVOYXZPcGVuZXIgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2lkZU5hdk9wZW5lcicpO1xuY29uc3Qgc2lkZU5hdkNsb3NlciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzaWRlTmF2Q2xvc2VyJyk7XG5jb25zdCBzaWRlTmF2TG9nb3V0Q2xvc2VyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NpZGVOYXZMb2dvdXRDbG9zZXInKTtcbmNvbnN0IHNpZGVOYXZCYWNrID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NpZGVOYXZCYWNrJyk7XG5cbmZ1bmN0aW9uIG9wZW5OYXYoKSB7XG4gICAgZGlzYWJsZVNjcm9sbCgpO1xuICAgIHNpZGVOYXYuc3R5bGUudHJhbnNmb3JtID0gJ3RyYW5zbGF0ZVgoMTAwJSknO1xuICAgIHNpZGVOYXZCYWNrLnN0eWxlLm9wYWNpdHkgPSAnMSc7XG4gICAgc2lkZU5hdkNsb3Nlci5zdHlsZS53aWR0aCA9ICcxMDAlJztcbn1cblxuZnVuY3Rpb24gY2xvc2VOYXYoKSB7XG4gICAgZW5hYmxlU2Nyb2xsKCk7XG4gICAgc2lkZU5hdi5zdHlsZS50cmFuc2Zvcm0gPSAndW5zZXQnO1xuICAgIHNpZGVOYXZCYWNrLnN0eWxlLm9wYWNpdHkgPSAnMCc7XG4gICAgc2lkZU5hdkNsb3Nlci5zdHlsZS53aWR0aCA9ICcwJztcbn1cblxuaWYgKHNpZGVOYXYpIHtcbiAgICBzaWRlTmF2T3BlbmVyLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgb3Blbk5hdik7XG4gICAgc2lkZU5hdkNsb3Nlci5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGNsb3NlTmF2KTtcbiAgICBpZiAoc2lkZU5hdkxvZ291dENsb3Nlcikge1xuICAgICAgICBzaWRlTmF2TG9nb3V0Q2xvc2VyLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgY2xvc2VOYXYpO1xuICAgIH1cbn1cblxuLy8gTWVkaWFFbGVtZW50IFBsYXllclxuXG5yZXF1aXJlKCdtZWRpYWVsZW1lbnQvYnVpbGQvbWVkaWFlbGVtZW50LWFuZC1wbGF5ZXIubWluJyk7XG5cbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCkge1xuICAgICQoJy5hdWRpby1wbGF5ZXIgYXVkaW8nKS5tZWRpYWVsZW1lbnRwbGF5ZXIoe1xuICAgICAgICBzdWNjZXNzOiBmdW5jdGlvbihwbGF5ZXIsIG5vZGUpIHtcbiAgICAgICAgICAgIC8vIE9wdGlvbmFsXG4gICAgICAgICAgICAkKHBsYXllcikuY2xvc2VzdCgnLm1lanNfX2NvbnRhaW5lcicpLmF0dHIoJ2xhbmcnLCBtZWpzLmkxOG4ubGFuZ3VhZ2UoKSk7XG4gICAgICAgICAgICAkKCdodG1sJykuYXR0cignbGFuZycsIG1lanMuaTE4bi5sYW5ndWFnZSgpKTtcbiAgICAgICAgICAgIC8vIE1vcmUgY29kZVxuICAgICAgICB9LFxuICAgICAgICBzdGFydFZvbHVtZTogMSxcbiAgICAgICAgYXV0b1Jld2luZDogdHJ1ZSxcbiAgICAgICAgZW5hYmxlUHJvZ3Jlc3NUb29sdGlwOiBmYWxzZSxcbiAgICAgICAgZmVhdHVyZXM6IFsncGxheXBhdXNlJywnW2ZlYXR1cmVfbmFtZV0nLCdjdXJyZW50JywncHJvZ3Jlc3MnLCdkdXJhdGlvbiddXG4gICAgfSlcbn0pO1xuXG4vLyBDb21tZW50IHJlcGx5XG5cbmNvbnN0IGNvbW1lbnRXcml0ZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5tZC1jb21tZW50LXdyaXRlJyk7XG5jb25zdCBjb21tZW50VGV4dEFyZWEgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnY29tbWVudF9tZXNzYWdlJyk7XG5jb25zdCBjb21tZW50UmVwbHkgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcuY29tbWVudC1yZXBseScpO1xuY29uc3QgcmVwbHlUbyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdjb21tZW50X3JlcGx5VG8nKTtcbmNvbnN0IHJlcGx5Rm9yID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2NvbW1lbnRfcmVwbHlGb3InKTtcbmNvbnN0IGNvbW1lbnRSZXBseVVzZXIgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcubWQtY29tbWVudC1yZXBseS11c2VyJyk7XG5jb25zdCByZXBseWluZ0RlbGV0ZSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5tZC1yZXBseWluZy1kZWxldGUnKTtcblxuaWYgKHJlcGx5aW5nRGVsZXRlKSB7XG4gICAgcmVwbHlpbmdEZWxldGUuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoKSA9PiB7XG4gICAgICAgIHJlcGx5VG8ucmVtb3ZlQXR0cmlidXRlKCd2YWx1ZScpO1xuICAgICAgICByZXBseUZvci5yZW1vdmVBdHRyaWJ1dGUoJ3ZhbHVlJyk7XG4gICAgICAgIGNvbW1lbnRXcml0ZS5jbGFzc0xpc3QucmVtb3ZlKCdtZC1yZXBseWluZycpO1xuICAgIH0pO1xufVxuXG5pZiAoY29tbWVudFJlcGx5KSB7XG4gICAgY29tbWVudFJlcGx5LmZvckVhY2goKHJlcGx5KSA9PiB7XG4gICAgICAgIGxldCByZXBseVVzZXIgPSByZXBseS5xdWVyeVNlbGVjdG9yKCcucmVwbHktdXNlcicpO1xuICAgICAgICBsZXQgcmVwbHlDb21tZW50ID0gcmVwbHkucXVlcnlTZWxlY3RvcignLnJlcGx5LWNvbW1lbnQnKTtcbiAgICAgICAgcmVwbHkuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAocmVwbHkpID0+IHtcbiAgICAgICAgICAgIHJlcGx5VG8udmFsdWUgPSByZXBseVVzZXIuaW5uZXJIVE1MO1xuICAgICAgICAgICAgcmVwbHlGb3IudmFsdWUgPSByZXBseUNvbW1lbnQuaW5uZXJIVE1MO1xuICAgICAgICAgICAgY29tbWVudFRleHRBcmVhLmZvY3VzKCk7XG5cbiAgICAgICAgICAgIGlmIChyZXBseVRvLnZhbHVlICE9PSAnJykge1xuICAgICAgICAgICAgICAgIGNvbW1lbnRXcml0ZS5jbGFzc0xpc3QuYWRkKCdtZC1yZXBseWluZycpO1xuICAgICAgICAgICAgICAgIGNvbW1lbnRSZXBseVVzZXIuaW5uZXJIVE1MID0gcmVwbHlUby52YWx1ZTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSlcbiAgICB9KTtcbn1cblxuLy8gU2VhcmNoZXJcblxubGV0IHNlYXJjaElucHV0cyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy5tZC1zZWFyY2gtYWxsLWlucHV0Jyk7XG5cbmNvbnN0IG5hdmJhclNlYXJjaCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCduYXZiYXJTZWFyY2gnKTtcbi8vIGNvbnN0IG5hdmJhclNlYXJjaElucHV0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ25hdmJhclNlYXJjaElucHV0Jyk7XG5jb25zdCBuYXZiYXJTZWFyY2hPcGVuZXIgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnb3Blbk5hdmJhclNlYXJjaCcpO1xuY29uc3QgbmF2YmFyU2VhcmNoQ2xvc2VyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2Nsb3NlTmF2YmFyU2VhcmNoJyk7XG5cbmZ1bmN0aW9uIG9wZW5OYXZiYXJTZWFyY2goKXtcbiAgICBuYXZiYXJTZWFyY2hPcGVuZXIuc3R5bGUuY3NzVGV4dCA9ICdkaXNwbGF5Om5vbmUgIWltcG9ydGFudCc7XG4gICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLm5hdmJhci1zZWFyY2gtbW9iaWxlJykuc3R5bGUuZGlzcGxheSA9ICd1bnNldCc7XG4gICAgZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLm5hdmJhci1zZWFyY2gtbW9iaWxlIGlucHV0JykuZm9jdXMoKTtcbn1cblxuZnVuY3Rpb24gY2xvc2VOYXZiYXJTZWFyY2goKXtcbiAgICBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCcubmF2YmFyLXNlYXJjaC1tb2JpbGUnKS5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgIG5hdmJhclNlYXJjaE9wZW5lci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xufVxuXG5pZiAobmF2YmFyU2VhcmNoKSB7XG4gICAgbmF2YmFyU2VhcmNoT3BlbmVyLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgb3Blbk5hdmJhclNlYXJjaCk7XG4gICAgbmF2YmFyU2VhcmNoQ2xvc2VyLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgY2xvc2VOYXZiYXJTZWFyY2gpO1xuICAgIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5ib2R5LXdyYXBwZXInKS5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIGNsb3NlTmF2YmFyU2VhcmNoKTtcbn1cblxuc2VhcmNoSW5wdXRzLmZvckVhY2goKGlucHV0Qm94LGtleSkgPT4ge1xuICAgIGlucHV0Qm94LnF1ZXJ5U2VsZWN0b3IoJy5zZWFyY2hfaW5wdXQnKS5hZGRFdmVudExpc3RlbmVyKCdpbnB1dCcsKGlucHV0KSA9PiB7XG4gICAgICAgIGlucHV0Qm94LnF1ZXJ5U2VsZWN0b3IoJy5zZWFyY2hfYnV0dG9uJykuaHJlZiA9ICcvc2VhcmNoLycgKyBpbnB1dC50YXJnZXQudmFsdWUucmVwbGFjZSgnIycsICclMjMnKS5yZXBsYWNlKCclJywgJyUyNScpO1xuICAgIH0pXG5cbiAgICBpbnB1dEJveC5xdWVyeVNlbGVjdG9yKCcuc2VhcmNoX2lucHV0JykuYWRkRXZlbnRMaXN0ZW5lcigna2V5dXAnLChldmVudCkgPT4ge1xuICAgICAgICAgICAgaWYgKGV2ZW50LmtleUNvZGUgPT09IDEzIHx8IGV2ZW50LmNvZGUgPT09IFwiRW50ZXJcIikge1xuICAgICAgICAgICAgICAgIGlucHV0Qm94LnF1ZXJ5U2VsZWN0b3IoJy5zZWFyY2hfYnV0dG9uJykuY2xpY2soKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICk7XG59KTtcblxuLy8gRW5hYmxlIHRvb2x0aXBcbiQoZnVuY3Rpb24gKCkge1xuICAgICQoJ1tkYXRhLXRvZ2dsZT1cInRvb2x0aXBcIl0nKS50b29sdGlwKHtcbiAgICAgICAgdHJpZ2dlciA6ICdob3ZlcidcbiAgICB9KVxufSk7XG5cbi8vIEltYWdlIG9uIGNoYW5nZVxuaWYgKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5jdXN0b20tZmlsZS1pbnB1dCcpKSB7XG4gICAgbGV0IGZpbGVJbnB1dCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5jdXN0b20tZmlsZS1pbnB1dCcpO1xuICAgIGlmIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncG9zdEltZ091dHB1dCcpKSB7XG4gICAgICAgIGxldCBwb3N0SW1nT3V0cHV0ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Bvc3RJbWdPdXRwdXQnKTtcbiAgICAgICAgZmlsZUlucHV0Lm9uY2hhbmdlID0gKCkgPT4ge1xuICAgICAgICAgICAgcG9zdEltZ091dHB1dC5zcmMgPSB3aW5kb3cuVVJMLmNyZWF0ZU9iamVjdFVSTChmaWxlSW5wdXQuZmlsZXNbMF0pO1xuICAgICAgICAgICAgcG9zdEltZ091dHB1dC5zdHlsZS5tYXJnaW5Cb3R0b20gPSAnOHB4JztcbiAgICAgICAgfTtcbiAgICB9IGVsc2UgaWYgKGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdvdXRwdXQtY29udGVudCcpKSB7XG4gICAgICAgIGxldCBvdXRwdXRDb250ZW50ID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ291dHB1dC1jb250ZW50Jyk7XG4gICAgICAgIGZpbGVJbnB1dC5vbmNoYW5nZSA9ICgpID0+IHtcbiAgICAgICAgICAgIG91dHB1dENvbnRlbnQuc3R5bGUuYmFja2dyb3VuZEltYWdlID0gJ3VybChcXCcnICsgd2luZG93LlVSTC5jcmVhdGVPYmplY3RVUkwoZmlsZUlucHV0LmZpbGVzWzBdKSArICdcXCcpJztcbiAgICAgICAgfTtcbiAgICB9XG59XG5cbi8vIFRleHRhcmVhIGF1dG9zaXplXG5pZiAoZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLm1kLWF1dG8tc2l6ZXInKSkge1xuICAgIGxldCB0ZXh0YXJlYSA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5tZC1hdXRvLXNpemVyJyk7XG4gICAgdGV4dGFyZWEub25pbnB1dCA9ICgpID0+IHtcbiAgICAgICAgdGV4dGFyZWEuc3R5bGUuaGVpZ2h0ID0gdGV4dGFyZWEuc2Nyb2xsSGVpZ2h0ICsgMiArIFwicHhcIjtcbiAgICB9O1xufVxuXG4vLyBBdXRvIGNsb3NlIGFsZXJ0c1xuJChcIi5tZC1hbGVydC1hdXRvLWhpZGVcIikuZmFkZVRvKDUwMDAsIDUwMCkuc2xpZGVVcCg1MDAsIGZ1bmN0aW9uKCl7XG4gICAgJChcIi5tZC1hbGVydC1hdXRvLWhpZGVcIikuc2xpZGVVcCg1MDApO1xufSk7XG5cbi8vIFByZXZlbnQgdXNlcm5hbWUgc3ltYm9sc1xuJCgnLnVzZXJuYW1lLWlucHV0Jykub24oJ2tleXByZXNzJywgZnVuY3Rpb24gKGV2ZW50KSB7XG4gICAgbGV0IHJlZ2V4ID0gbmV3IFJlZ0V4cChcIl5bYS16QS1aMC05Ll9dKyRcIik7XG4gICAgbGV0IGtleSA9IFN0cmluZy5mcm9tQ2hhckNvZGUoIWV2ZW50LmNoYXJDb2RlID8gZXZlbnQud2hpY2ggOiBldmVudC5jaGFyQ29kZSk7XG4gICAgaWYgKCFyZWdleC50ZXN0KGtleSkpIHtcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH1cbn0pO1xuXG4vLyBQb3N0IGNvbGxhcHNlXG5jb25zdCBwb3N0Q29sbGFwc2UgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcucG9zdC1jb2xsYXBzZScpO1xuXG5wb3N0Q29sbGFwc2UuZm9yRWFjaCgocG9zdCkgPT4ge1xuICAgIGZ1bmN0aW9uIG9wZW5Db2xsYXBzZWRQb3N0KCkge1xuICAgICAgICB0aGlzLnN0eWxlLmRpc3BsYXkgPSAnbm9uZSc7XG4gICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdwb3N0Q29sbGFwc2UnICsgcG9zdElkKS5jbGFzc0xpc3QucmVtb3ZlKCdjbG9zZWQnKTtcbiAgICB9XG5cbiAgICBsZXQgcG9zdElkID0gcG9zdC5pZC5yZXBsYWNlKCdwb3N0Q29sbGFwc2UnLCAnJyk7XG5cbiAgICBpZiAocG9zdC5xdWVyeVNlbGVjdG9yKCdkaXYnKS5jbGllbnRIZWlnaHQgPiA1MCkge1xuICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgncG9zdENvbGxhcHNlQnV0dG9uJyArIHBvc3RJZCkuc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG4gICAgfVxuXG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3Bvc3RDb2xsYXBzZUJ1dHRvbicgKyBwb3N0SWQpLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgb3BlbkNvbGxhcHNlZFBvc3QpO1xufSk7XG5cbi8vIE1vZGFsIGNoYW5nZSBib2R5IGNvbG9yXG5cbi8vIGxldCBvYnNlcnZlciA9IG5ldyBNdXRhdGlvbk9ic2VydmVyKChtdXRhdGlvbnMpID0+IHtcbi8vICAgICBtdXRhdGlvbnMuZm9yRWFjaChmdW5jdGlvbihtdXRhdGlvbikge1xuLy8gICAgICAgICBpZiAobXV0YXRpb24uYXR0cmlidXRlTmFtZSA9PT0gXCJjbGFzc1wiKSB7XG4vLyAgICAgICAgICAgICBpZiAobW9iaWxlTWVkaWEubWF0Y2hlcykge1xuLy8gICAgICAgICAgICAgICAgIGlmICgkKG11dGF0aW9uLnRhcmdldCkuaGFzQ2xhc3MoJ21vZGFsLW9wZW4nKSl7XG4vLyAgICAgICAgICAgICAgICAgICAgIG9ic2VydmVkQm9keS5zdHlsZS5iYWNrZ3JvdW5kQ29sb3IgPSAnIzE0MTQxNCc7XG4vLyAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbi8vICAgICAgICAgICAgICAgICAgICAgb2JzZXJ2ZWRCb2R5LnN0eWxlLmJhY2tncm91bmRDb2xvciA9ICcjMjgyODI3Jztcbi8vICAgICAgICAgICAgICAgICB9XG4vLyAgICAgICAgICAgICB9XG4vLyAgICAgICAgIH1cbi8vICAgICB9KTtcbi8vIH0pO1xuLy9cbi8vIGxldCBtb2JpbGVNZWRpYSA9IHdpbmRvdy5tYXRjaE1lZGlhKFwiKG1heC13aWR0aDogNTc2cHgpXCIpXG4vLyBsZXQgb2JzZXJ2ZWRCb2R5ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignYm9keScpO1xuLy9cbi8vIG9ic2VydmVyLm9ic2VydmUob2JzZXJ2ZWRCb2R5LCB7XG4vLyAgICAgYXR0cmlidXRlczogdHJ1ZVxuLy8gfSk7XG4iLCJjb25zdCBheGlvcyA9IHJlcXVpcmUoJ2F4aW9zJykuZGVmYXVsdDtcblxuLy8gLS0tLS0tLS0tLS0tLS0tLS0tLS0gU2hhcmUgZ2xvYmFsIGZ1bmN0aW9ucyAtLS0tLS0tLS0tLS0tLS0tLS0tLSAvL1xuXG5mdW5jdGlvbiBzaGFyZU1lZGlhQWxlcnQoZXZlbnQsdXNlcix0eXBlKSB7XG5cbiAgICBmdW5jdGlvbiBzaGFyZVR5cGUodHlwZSkge1xuICAgICAgICBpZiAodHlwZSA9PT0gJ1Byb2ZpbGUnKSB7XG4gICAgICAgICAgICByZXR1cm4gdXNlci5xdWVyeVNlbGVjdG9yKCcubWQtdXNlci1pZCcpLmlubmVySFRNTDtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiB1c2VyLnF1ZXJ5U2VsZWN0b3IoJy5tZC11c2VybmFtZScpLmlubmVySFRNTDtcbiAgICAgICAgfVxuICAgIH1cbiAgICBsZXQgdXNlcm5hbWUgPSBzaGFyZVR5cGUodHlwZSk7XG4gICAgbGV0IG1lZGlhID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlJyArIHR5cGUgKyAnSWQnKS5pbm5lckhUTUw7XG4gICAgbGV0IHVybCA9ICcvc2hhcmUnICsgdHlwZSArICcvJyArIG1lZGlhICsgJy8nICsgdXNlcm5hbWU7XG5cbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmUnICsgdHlwZSArICdVc2VybmFtZScpLmlubmVySFRNTCA9IHVzZXIucXVlcnlTZWxlY3RvcignLm1kLXVzZXJuYW1lJykuaW5uZXJIVE1MO1xuICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzaGFyZScgKyB0eXBlICsgJ0FsZXJ0JykucXVlcnlTZWxlY3RvcignYScpLmhyZWYgPSB1cmw7XG5cbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmUnICsgdHlwZSArICdNb2RhbCcpLmNsaWNrKCk7XG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlJyArIHR5cGUgKyAnQ29uZmlybScpLmNsaWNrKCk7XG59XG5cbmZ1bmN0aW9uIHNoYXJlTWVkaWEoZXZlbnQsIHR5cGUpIHtcbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIGxldCB1cmwgPSBldmVudC50YXJnZXQuaHJlZjtcblxuICAgIGF4aW9zLmdldCh1cmwpLnRoZW4oKHJlc3BvbnNlKSA9PiB7XG4gICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzaGFyZScgKyB0eXBlICsgJ0FsZXJ0JykuY2xpY2soKTtcblxuICAgICAgICBsZXQgbWVzc2FnZSA9ICc8ZGl2IGNsYXNzPVwibWQtYWxlcnQgbWQtYWxlcnQtc3VjY2VzcyBtZC1ib3gtbWJcIj4nICtcbiAgICAgICAgICAgIHJlc3BvbnNlLmRhdGEucmVzcG9uc2UubWVzc2FnZSArXG4gICAgICAgICAgICAnPC9kaXY+JztcblxuICAgICAgICBsZXQgYWxlcnRCb3ggPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCdtYWluJyk7XG4gICAgICAgIGxldCBhbGVydEV4aXN0ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLm1kLWFsZXJ0Jyk7XG5cbiAgICAgICAgaWYgKGFsZXJ0RXhpc3QpIHtcbiAgICAgICAgICAgIGFsZXJ0RXhpc3Qub3V0ZXJIVE1MID0gbWVzc2FnZTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGFsZXJ0Qm94Lmluc2VydEFkamFjZW50SFRNTCgnYWZ0ZXJiZWdpbicsIG1lc3NhZ2UpO1xuICAgICAgICB9XG5cbiAgICAgICAgJChcIi5tZC1hbGVydFwiKS5mYWRlVG8oMzAwMCwgNTAwKS5zbGlkZVVwKDUwMCwgZnVuY3Rpb24oKXtcbiAgICAgICAgICAgICQoXCIubWQtYWxlcnRcIikuc2xpZGVVcCg1MDApO1xuICAgICAgICB9KTtcbiAgICB9KVxufVxuXG4vLyAtLS0tLS0tLS0tLS0tLS0tLS0tLSBTaGFyZSBzb25nIC0tLS0tLS0tLS0tLS0tLS0tLS0tIC8vXG5cbi8vIFNvbmcgc2hhcmUgYWxlcnRcbmxldCBzaGFyZVNvbmdVc2VycyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJyNzaGFyZVNvbmdNb2RhbCAudXNlci1saW5lJyk7XG5zaGFyZVNvbmdVc2Vycy5mb3JFYWNoKCh1c2VyKSA9PiB7XG4gICAgdXNlci5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIChldmVudCkgPT4ge1xuICAgICAgICBzaGFyZU1lZGlhQWxlcnQoZXZlbnQsdXNlciwnU29uZycpO1xuICAgIH0pXG59KTtcblxuLy8gSWYgc29uZyBzaGFyZSBjb25maXJtXG5sZXQgc2hhcmVTb25nQnV0dG9uID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlU29uZ0NvbmZpcm1CdXR0b24nKTtcbmlmIChzaGFyZVNvbmdCdXR0b24pIHtcbiAgICBzaGFyZVNvbmdCdXR0b24uYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoZXZlbnQpID0+IHtcbiAgICAgICAgc2hhcmVNZWRpYShldmVudCwnU29uZycpO1xuICAgIH0pO1xufVxuXG4vLyBJZiBzb25nIHNoYXJlIGNhbmNlbFxuZnVuY3Rpb24gc2hhcmVTb25nQ2FuY2VsRnVuY3Rpb24oZXZlbnQpIHtcbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzaGFyZVNvbmdBbGVydCcpLmNsaWNrKCk7XG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlU29uZycpLmNsaWNrKCk7XG59XG5cbmxldCBzaGFyZVNvbmdDYW5jZWwgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmVTb25nQ2FuY2VsJyk7XG5pZiAoc2hhcmVTb25nQ2FuY2VsKSB7XG4gICAgc2hhcmVTb25nQ2FuY2VsLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgc2hhcmVTb25nQ2FuY2VsRnVuY3Rpb24pO1xufVxuXG4vLyAtLS0tLS0tLS0tLS0tLS0tLS0tLSBTaGFyZSBwb3N0IC0tLS0tLS0tLS0tLS0tLS0tLS0tIC8vXG5cbi8vIEluc2VydCBwb3N0IElkXG5mdW5jdGlvbiBzaGFyZVBvc3RCdXR0b25GdW5jdGlvbigpIHtcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmVQb3N0SWQnKS5pbm5lckhUTUwgPSB0aGlzLmlkLnJlcGxhY2UoJ3NoYXJlUG9zdElkJywgJycpO1xufVxuXG5sZXQgc2hhcmVQb3N0QnV0dG9uID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLnNoYXJlLXBvc3QtYnV0dG9uJyk7XG5zaGFyZVBvc3RCdXR0b24uZm9yRWFjaCgoc2hhcmVQb3N0QnV0dG9uKSA9PiB7XG4gICAgc2hhcmVQb3N0QnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgc2hhcmVQb3N0QnV0dG9uRnVuY3Rpb24pXG59KTtcblxuLy8gUG9zdCBzaGFyZSBhbGVydFxubGV0IHNoYXJlUG9zdFVzZXJzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnI3NoYXJlUG9zdE1vZGFsIC51c2VyLWxpbmUnKTtcbnNoYXJlUG9zdFVzZXJzLmZvckVhY2goKHVzZXIpID0+IHtcbiAgICB1c2VyLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgKGV2ZW50KSA9PiB7XG4gICAgICAgIHNoYXJlTWVkaWFBbGVydChldmVudCx1c2VyLCdQb3N0Jyk7XG4gICAgfSlcbn0pXG5cbi8vIElmIHBvc3Qgc2hhcmUgY29uZmlybVxubGV0IHNoYXJlUG9zdENvbmZpcm1CdXR0b24gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmVQb3N0Q29uZmlybUJ1dHRvbicpO1xuaWYgKHNoYXJlUG9zdENvbmZpcm1CdXR0b24pIHtcbiAgICBzaGFyZVBvc3RDb25maXJtQnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgKGV2ZW50KSA9PiB7XG4gICAgICAgIHNoYXJlTWVkaWEoZXZlbnQsJ1Bvc3QnKTtcbiAgICB9KVxufVxuXG4vLyBJZiBwb3N0IHNoYXJlIGNhbmNlbFxuZnVuY3Rpb24gc2hhcmVQb3N0Q2FuY2VsRnVuY3Rpb24oKSB7XG4gICAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlUG9zdEFsZXJ0JykuY2xpY2soKTtcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmVQb3N0SWQnICsgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlUG9zdElkJykuaW5uZXJIVE1MKS5jbGljaygpO1xufVxuXG5sZXQgc2hhcmVQb3N0Q2FuY2VsID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlUG9zdENhbmNlbCcpO1xuaWYgKHNoYXJlUG9zdENhbmNlbCkge1xuICAgIHNoYXJlUG9zdENhbmNlbC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIHNoYXJlUG9zdENhbmNlbEZ1bmN0aW9uKTtcbn1cblxuLy8gLS0tLS0tLS0tLS0tLS0tLS0tLS0gU2hhcmUgcHJvZmlsZSAtLS0tLS0tLS0tLS0tLS0tLS0tLSAvL1xuXG4vLyBQcm9maWxlIHNoYXJlIGFsZXJ0XG5sZXQgc2hhcmVQcm9maWxlVXNlcnMgPSBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCcjc2hhcmVQcm9maWxlTW9kYWwgLnVzZXItbGluZScpO1xuc2hhcmVQcm9maWxlVXNlcnMuZm9yRWFjaCgodXNlcikgPT4ge1xuICAgIHVzZXIuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCAoZXZlbnQpID0+IHtcbiAgICAgICAgc2hhcmVNZWRpYUFsZXJ0KGV2ZW50LHVzZXIsJ1Byb2ZpbGUnKTtcbiAgICB9KVxufSk7XG5cbi8vIElmIHByb2ZpbGUgc2hhcmUgY29uZmlybVxubGV0IHNoYXJlUHJvZmlsZUJ1dHRvbiA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzaGFyZVByb2ZpbGVDb25maXJtQnV0dG9uJyk7XG5pZiAoc2hhcmVQcm9maWxlQnV0dG9uKSB7XG4gICAgc2hhcmVQcm9maWxlQnV0dG9uLmFkZEV2ZW50TGlzdGVuZXIoJ2NsaWNrJywgKGV2ZW50KSA9PiB7XG4gICAgICAgIHNoYXJlTWVkaWEoZXZlbnQsJ1Byb2ZpbGUnKTtcbiAgICB9KTtcbn1cblxuLy8gSWYgcHJvZmlsZSBzaGFyZSBjYW5jZWxcbmZ1bmN0aW9uIHNoYXJlUHJvZmlsZUNhbmNlbEZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcbiAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2hhcmVQcm9maWxlQWxlcnQnKS5jbGljaygpO1xuICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzaGFyZVByb2ZpbGUnKS5jbGljaygpO1xufVxuXG5sZXQgc2hhcmVQcm9maWxlQ2FuY2VsID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ3NoYXJlUHJvZmlsZUNhbmNlbCcpO1xuaWYgKHNoYXJlUHJvZmlsZUNhbmNlbCkge1xuICAgIHNoYXJlUHJvZmlsZUNhbmNlbC5hZGRFdmVudExpc3RlbmVyKCdjbGljaycsIHNoYXJlUHJvZmlsZUNhbmNlbEZ1bmN0aW9uKTtcbn1cbiIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpblxuZXhwb3J0IHt9OyJdLCJuYW1lcyI6WyJheGlvcyIsInJlcXVpcmUiLCJwbGF5bGlzdCIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvckFsbCIsImJvb2ttYXJrIiwic3dpdGNoZXIiLCJldmVudCIsInByZXZlbnREZWZhdWx0IiwidXJsIiwiaHJlZiIsImdldCIsInRoZW4iLCJyZXNwb25zZSIsInN0YXR1cyIsIlN0cmluZyIsImRhdGEiLCJjbGFzc0xpc3QiLCJhZGQiLCJyZW1vdmUiLCJkYXRhc2V0Iiwib3JpZ2luYWxUaXRsZSIsInRpdGxlIiwic3R5bGUiLCJwb2ludGVyRXZlbnRzIiwiYWxlcnRCb3giLCJxdWVyeVNlbGVjdG9yIiwiYWxlcnRFeGlzdCIsIm1lc3NhZ2UiLCJiYWRnZSIsIm91dGVySFRNTCIsImluc2VydEFkamFjZW50SFRNTCIsIiQiLCJmYWRlVG8iLCJzbGlkZVVwIiwic2V0VGltZW91dCIsImZvckVhY2giLCJhZGRFdmVudExpc3RlbmVyIiwiZm9sbG93IiwiZm9sbG93cyIsImdldEVsZW1lbnRCeUlkIiwiZm9sbG93ZXJzIiwiaW5uZXJIVE1MIiwicGFyc2VJbnQiLCJ0b1N0cmluZyIsInVuZm9sbG93IiwidW5mb2xsb3dzIiwiZm9sbG93ZXJCbG9jayIsImlkIiwic3BsaXQiLCJwb3AiLCJjYW5jZWxCdXR0b25JZCIsImNsaWNrIiwicmVqZWN0UmVxdWVzdEZ1bmN0aW9uIiwicmVxdWVzdFJlc3BvbnNlIiwidGFyZ2V0IiwicmVxdWVzdCIsImxvY2F0aW9uIiwicmVsb2FkIiwiZGlzcGxheSIsImFjY2VwdFJlcXVlc3QiLCJyZWplY3RSZXF1ZXN0IiwiZmVhdHVyZWQiLCJmZWF0dXJlZFBvc3QiLCJsaWtlIiwibGlrZXIiLCJjb250YWlucyIsInZhbHVlIiwiaW5jbHVkZXMiLCJsaWtlQ291bnRlciIsInJlcGxhY2UiLCJjdXJyZW50TGlrZXMiLCJwYXJlbnRFbGVtZW50IiwicG9zdCIsInBvc3RJZCIsInBvc3RMaWtlciIsInBvc3RMaWtlQ291bnRlciIsImdsb2JhbCIsImpRdWVyeSIsImtleXMiLCJlIiwicHJldmVudERlZmF1bHRGb3JTY3JvbGxLZXlzIiwia2V5Q29kZSIsInN1cHBvcnRzUGFzc2l2ZSIsIndpbmRvdyIsIk9iamVjdCIsImRlZmluZVByb3BlcnR5Iiwid2hlZWxPcHQiLCJwYXNzaXZlIiwid2hlZWxFdmVudCIsImNyZWF0ZUVsZW1lbnQiLCJkaXNhYmxlU2Nyb2xsIiwiZW5hYmxlU2Nyb2xsIiwicmVtb3ZlRXZlbnRMaXN0ZW5lciIsInNpZGVOYXYiLCJzaWRlTmF2T3BlbmVyIiwic2lkZU5hdkNsb3NlciIsInNpZGVOYXZMb2dvdXRDbG9zZXIiLCJzaWRlTmF2QmFjayIsIm9wZW5OYXYiLCJ0cmFuc2Zvcm0iLCJvcGFjaXR5Iiwid2lkdGgiLCJjbG9zZU5hdiIsInJlYWR5IiwibWVkaWFlbGVtZW50cGxheWVyIiwic3VjY2VzcyIsInBsYXllciIsIm5vZGUiLCJjbG9zZXN0IiwiYXR0ciIsIm1lanMiLCJpMThuIiwibGFuZ3VhZ2UiLCJzdGFydFZvbHVtZSIsImF1dG9SZXdpbmQiLCJlbmFibGVQcm9ncmVzc1Rvb2x0aXAiLCJmZWF0dXJlcyIsImNvbW1lbnRXcml0ZSIsImNvbW1lbnRUZXh0QXJlYSIsImNvbW1lbnRSZXBseSIsInJlcGx5VG8iLCJyZXBseUZvciIsImNvbW1lbnRSZXBseVVzZXIiLCJyZXBseWluZ0RlbGV0ZSIsInJlbW92ZUF0dHJpYnV0ZSIsInJlcGx5IiwicmVwbHlVc2VyIiwicmVwbHlDb21tZW50IiwiZm9jdXMiLCJzZWFyY2hJbnB1dHMiLCJuYXZiYXJTZWFyY2giLCJuYXZiYXJTZWFyY2hPcGVuZXIiLCJuYXZiYXJTZWFyY2hDbG9zZXIiLCJvcGVuTmF2YmFyU2VhcmNoIiwiY3NzVGV4dCIsImNsb3NlTmF2YmFyU2VhcmNoIiwiaW5wdXRCb3giLCJrZXkiLCJpbnB1dCIsImNvZGUiLCJ0b29sdGlwIiwidHJpZ2dlciIsImZpbGVJbnB1dCIsInBvc3RJbWdPdXRwdXQiLCJvbmNoYW5nZSIsInNyYyIsIlVSTCIsImNyZWF0ZU9iamVjdFVSTCIsImZpbGVzIiwibWFyZ2luQm90dG9tIiwib3V0cHV0Q29udGVudCIsImJhY2tncm91bmRJbWFnZSIsInRleHRhcmVhIiwib25pbnB1dCIsImhlaWdodCIsInNjcm9sbEhlaWdodCIsIm9uIiwicmVnZXgiLCJSZWdFeHAiLCJmcm9tQ2hhckNvZGUiLCJjaGFyQ29kZSIsIndoaWNoIiwidGVzdCIsInBvc3RDb2xsYXBzZSIsIm9wZW5Db2xsYXBzZWRQb3N0IiwiY2xpZW50SGVpZ2h0Iiwic2hhcmVNZWRpYUFsZXJ0IiwidXNlciIsInR5cGUiLCJzaGFyZVR5cGUiLCJ1c2VybmFtZSIsIm1lZGlhIiwic2hhcmVNZWRpYSIsInNoYXJlU29uZ1VzZXJzIiwic2hhcmVTb25nQnV0dG9uIiwic2hhcmVTb25nQ2FuY2VsRnVuY3Rpb24iLCJzaGFyZVNvbmdDYW5jZWwiLCJzaGFyZVBvc3RCdXR0b25GdW5jdGlvbiIsInNoYXJlUG9zdEJ1dHRvbiIsInNoYXJlUG9zdFVzZXJzIiwic2hhcmVQb3N0Q29uZmlybUJ1dHRvbiIsInNoYXJlUG9zdENhbmNlbEZ1bmN0aW9uIiwic2hhcmVQb3N0Q2FuY2VsIiwic2hhcmVQcm9maWxlVXNlcnMiLCJzaGFyZVByb2ZpbGVCdXR0b24iLCJzaGFyZVByb2ZpbGVDYW5jZWxGdW5jdGlvbiIsInNoYXJlUHJvZmlsZUNhbmNlbCJdLCJzb3VyY2VSb290IjoiIn0=