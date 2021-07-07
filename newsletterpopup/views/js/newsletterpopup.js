const close = document.querySelector("#newsletter_popup .close");
const newsletterPopup = document.querySelector("#newsletter_popup");

function removePopup () {
    newsletterPopup.remove();
}

close.addEventListener("click", removePopup);
