document.addEventListener("DOMContentLoaded", function () {
    const errorBox = document.getElementById("errorBox");
    const inputs = document.querySelectorAll("input");

    inputs.forEach(function (input) {
        input.addEventListener("focus", function () {
            if (errorBox) {
                errorBox.style.display = "none";
            }
        });
    });
});

function toggleMenu(x){
    alert("clicked");
    x.classList.toggle("change");
}