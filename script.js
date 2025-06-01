document.getElementById("loginForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let studentNumber = document.getElementById("studentNumber").value;
    let password = document.getElementById("password").value;
    let errorMessage = document.getElementById("errorMessage");

    fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `studentNumber=${studentNumber}&password=${password}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = "index.html";
            alert("The System is Hacked!");
            alert("Initializing Anti Hacking... 0%");
            alert("Initializing Anti Hacking... 25%");
            alert("Initializing Anti Hacking... 26%");
            alert("Initializing Anti Hacking... 30%");
            alert("Initializing Anti Hacking... 45%");
            alert("Initializing Anti Hacking... 55%");
            alert("Initializing Anti Hacking... 65%");
            alert("Initializing Anti Hacking... 70%");
            alert("Initializing Anti Hacking... 80%");
            alert("Initializing Anti Hacking... 90%");
            alert("Initializing Anti Hacking... 99%");
            alert("Initializing Anti Hacking... 99%");
            alert("Initializing Anti Hacking... 99%");
            alert("Initializing Anti Hacking... 99%");
            alert("Initializing Anti Hacking... 99%");
            alert("Initializing Anti Hacking... 100%");
            alert("Hacking Supressed Successfully!");
        } else {
            errorMessage.textContent = data.message;
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
});

