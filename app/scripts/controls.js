function moveRight() {
    var character = document.getElementById("character");
    var left = parseInt(character.style.marginLeft);
    character.style.marginLeft = left + 5 + "px";
}

function moveLeft() {
    var character = document.getElementById("character");
    var left = parseInt(character.style.marginLeft);
    character.style.marginLeft = left - 5 + "px";
}

function explose() {
    $('#character').toggle("explode");
}
