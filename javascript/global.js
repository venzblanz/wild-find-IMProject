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
    x.classList.toggle("change");
}
function toggleSidebar(menuButton) {
    const sidebar = document.getElementById("sidebar");

    menuButton.classList.toggle("change");

    if (sidebar) {
        sidebar.classList.toggle("closed");
    }
}