document.addEventListener("DOMContentLoaded", function(event) {
    var classname = document.getElementsByClassName("sn");
    for (var i = 0; i < classname.length; i++) {
        classname[i].addEventListener('click', decodeAffiliateLink, false);
    }
});

var decodeAffiliateLink = function(event) {
    var attribute = this.getAttribute("data-sn");
    if(event.ctrlKey) {
        var newWindow = window.open(decodeURIComponent(window.atob(attribute)), '_blank');
        newWindow.focus();
    } else {
        window.open(decodeURIComponent(window.atob(attribute)),'_blank');
    }
}