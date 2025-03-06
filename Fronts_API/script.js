// Connexion utilisateur
function login() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            localStorage.setItem("token", data.token); // Stocke le token
            localStorage.setItem("user", JSON.stringify(data.user)); // Stocke les infos user
            window.location.href = "dashboard.html"; // Redirection vers le tableau de bord
        } else {
            alert(data.error);
        }
    })
    .catch(error => console.error("Erreur:", error));
}

// Déconnexion
function logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "index.html";
}

// Charger les infos utilisateur sur `dashboard.html`
function loadUserInfo() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "index.html"; // Redirection si pas connecté
        return;
    }

    fetch("get_user.php", {
        method: "GET",
        headers: { "Authorization": "Bearer " + token }
    })
    .then(response => response.json())
    .then(data => {
        if (data.user) {
            document.getElementById("username").textContent = data.user.username;
            document.getElementById("user-email").textContent = data.user.email;
        } else {
            alert("Erreur : " + data.error);
            logout();
        }
    })
    .catch(error => console.error("Erreur:", error));
}
