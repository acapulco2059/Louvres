class Bouton{
    constructor(name){
        window.louvres[name] = this;
        this.name = name;
        this.DOM = document.createElement("button");
        this.render();
    }

    render(){
        this.DOM.innerHTML = "cliquez-moi";
        this.DOM.className = "maSuperClasse";
        this.DOM.onclick   = function(){
            let a = window.louvres[this.name].test();
            console.log(a);
        }.bind(this);
    }
    test(){
        return this.name;
    }
};