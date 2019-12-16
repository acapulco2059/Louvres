window.louvres = {};
document.body.onload = function(){
    new Page(document.querySelector('main'), "127.0.0.1:8000");
}