const url = require("url");
const axios = require('axios').default;

// -------------------- Share global functions -------------------- //

function shareMediaAlert(event,user,type) {

    function shareType(type) {
        if (type === 'Profile') {
            return user.querySelector('.md-user-id').innerHTML;
        } else {
            return user.querySelector('.md-username').innerHTML;
        }
    }
    let username = shareType(type);
    let media = document.getElementById('share' + type + 'Id').innerHTML;
    let url = '/share' + type + '/' + media + '/' + username;

    document.getElementById('share' + type + 'Username').innerHTML = user.querySelector('.md-username').innerHTML;
    document.getElementById('share' + type + 'Alert').querySelector('a').href = url;

    document.getElementById('share' + type + 'Modal').click();
    document.getElementById('share' + type + 'Confirm').click();
}

function shareMedia(event, type) {
    event.preventDefault();
    let url = event.target.href;

    axios.get(url).then((response) => {
        document.getElementById('share' + type + 'Alert').click();

        let message = '<div class="md-alert md-alert-success md-box-mb">' +
            response.data.response.message +
            '</div>';

        let alertBox = document.querySelector('main');
        let alertExist = document.querySelector('.md-alert');

        if (alertExist) {
            alertExist.outerHTML = message;
        } else {
            alertBox.insertAdjacentHTML('afterbegin', message);
        }

        $(".md-alert").fadeTo(3000, 500).slideUp(500, function(){
            $(".md-alert").slideUp(500);
        });
    })
}

function shareUsers(shareButton, type) {
    shareButton.addEventListener('click', (event) => {
        let shareContent = document.querySelector('.user-share-media.' + type.toLowerCase());
        if (shareContent.innerHTML === '') {
            shareContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i></div>';
            let url = '/shareUsers';

            axios.get(url).then((response) => {
                shareContent.innerHTML = '';
                response.data.users.forEach((user) => {
                    let userLine = document.createElement('div');
                    userLine.classList.add('user-line');
                    userLine.innerHTML =
                        `<div class="back-picture bp-45" style="margin-right: 10px; background-image: url('${user.image}')"></div>
                            <div class="user-line-info">
                                <div class="user-line-username">
                                    <span class="md-username">${user.username}</span>
                                </div>
                            </div>
                            `;

                    if (type === 'Profile') {
                        userLine.insertAdjacentHTML('afterbegin',`<span style="display: none" class="md-user-id">${user.id}</span>`)
                    }

                    document.querySelector('.user-share-media.' + type.toLowerCase()).appendChild(userLine);
                })

                let shareUsers = document.querySelectorAll('.user-share-modal .user-line');
                shareUsers.forEach((user) => {
                    user.addEventListener('click', (event) => {
                        shareMediaAlert(event,user,type);
                    })
                })
            })
        }
    })
}

// -------------------- Share song -------------------- //

// Song share alert
if (document.getElementById('shareSong')) {
    shareUsers(document.getElementById('shareSong'),'Song');
}

// If song share confirm
let shareSongButton = document.getElementById('shareSongConfirmButton');
if (shareSongButton) {
    shareSongButton.addEventListener('click', (event) => {
        shareMedia(event,'Song');
    });
}

// If song share cancel
function shareSongCancelFunction(event) {
    event.preventDefault();
    document.getElementById('shareSongAlert').click();
    document.getElementById('shareSong').click();
}

let shareSongCancel = document.getElementById('shareSongCancel');
if (shareSongCancel) {
    shareSongCancel.addEventListener('click', shareSongCancelFunction);
}

// -------------------- Share post -------------------- //

// Insert post Id
function sharePostButtonFunction() {
    document.getElementById('sharePostId').innerHTML = this.id.replace('sharePostId', '');
}

let sharePostButton = document.querySelectorAll('.share-post-button');
sharePostButton.forEach((sharePostButton) => {
    sharePostButton.addEventListener('click', sharePostButtonFunction)
});

// Post share alert
if (document.querySelector('.share-post-button')) {
    sharePostButton.forEach((shareButton) => {
        shareUsers(shareButton,'Post')
    });
}

// If post share confirm
let sharePostConfirmButton = document.getElementById('sharePostConfirmButton');
if (sharePostConfirmButton) {
    sharePostConfirmButton.addEventListener('click', (event) => {
        shareMedia(event,'Post');
    })
}

// If post share cancel
function sharePostCancelFunction() {
    document.getElementById('sharePostAlert').click();
    document.getElementById('sharePostId' + document.getElementById('sharePostId').innerHTML).click();
}

let sharePostCancel = document.getElementById('sharePostCancel');
if (sharePostCancel) {
    sharePostCancel.addEventListener('click', sharePostCancelFunction);
}

// -------------------- Share profile -------------------- //

if (document.getElementById('shareProfile')) {
    shareUsers(document.getElementById('shareProfile'),'Profile');
}

// If profile share confirm
let shareProfileButton = document.getElementById('shareProfileConfirmButton');
if (shareProfileButton) {
    shareProfileButton.addEventListener('click', (event) => {
        shareMedia(event,'Profile');
    });
}

// If profile share cancel
function shareProfileCancelFunction(event) {
    event.preventDefault();
    document.getElementById('shareProfileAlert').click();
    document.getElementById('shareProfile').click();
}

let shareProfileCancel = document.getElementById('shareProfileCancel');
if (shareProfileCancel) {
    shareProfileCancel.addEventListener('click', shareProfileCancelFunction);
}
