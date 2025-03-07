// Connexion utilisateur
function login() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    fetch("http://localhost/projapi/login.php", {
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

// Charger les infos utilisateur
function loadUserInfo() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "index.html"; // Redirection si pas connecté
        return;
    }

    fetch("http://localhost/projapi/get_user.php", {
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

// Afficher ou masquer le mot de passe
document.getElementById("showPassword").addEventListener("change", function () {
    const passwordField = document.getElementById("newPassword");
    passwordField.type = this.checked ? "text" : "password";
});

// Mettre à jour les informations utilisateur
document.getElementById("userSettingsForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "index.html";
        return;
    }

    const newUsername = document.getElementById("newUsername").value.trim();
    const newEmail = document.getElementById("newEmail").value.trim();
    const newPassword = document.getElementById("newPassword").value.trim();

    if (!newUsername && !newEmail && !newPassword) {
        document.getElementById("updateMessage").textContent = "Veuillez remplir au moins un champ.";
        document.getElementById("updateMessage").style.color = "red";
        return;
    }

    const updateData = {};
    if (newUsername) updateData.username = newUsername;
    if (newEmail) updateData.email = newEmail;
    if (newPassword) updateData.password = newPassword;

    try {
        const response = await fetch("http://localhost/projapi/update_user.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + token
            },
            body: JSON.stringify(updateData)
        });

        const data = await response.json();
        if (data.success) {
            document.getElementById("updateMessage").textContent = "Mise à jour réussie !";
            document.getElementById("updateMessage").style.color = "green";

            loadUserInfo();
        } else {
            document.getElementById("updateMessage").textContent = "Erreur : " + data.error;
            document.getElementById("updateMessage").style.color = "red";
        }
    } catch (error) {
        document.getElementById("updateMessage").textContent = "Erreur serveur.";
        document.getElementById("updateMessage").style.color = "red";
    }
});



document.addEventListener("DOMContentLoaded", function () {
    loadOrders(); // Charger les commandes existantes au chargement de la page
});

// Charger les commandes depuis l'API
async function loadOrders() {
    const token = localStorage.getItem("token");

    if (!token) {
        alert("Vous devez être connecté.");
        window.location.href = "index.html";
        return;
    }

    try {
        const response = await fetch("http://localhost/projapi/orders.php", {
            method: "GET",
            headers: { "Authorization": "Bearer " + token }
        });

        const data = await response.json();

        if (data.success) {
            displayOrders(data.orders);
        } else {
            alert("Erreur : " + data.error);
        }
    } catch (error) {
        console.error("Erreur de chargement des commandes:", error);
    }
}

// Afficher les commandes sur la page
function displayOrders(orders) {
    const ordersList = document.getElementById('ordersList');
    ordersList.innerHTML = '';

    orders.forEach(order => {
        const li = document.createElement('li');
        li.classList.add('order-item');
        li.innerHTML = `
            <span>${order.product} (Quantité: ${order.quantity})</span>
            <button class="btn delete-btn" onclick="deleteOrder(${order.id})">Supprimer</button>
        `;
        ordersList.appendChild(li);
    });
}

// Ajouter une commande via l'API
document.getElementById('orderForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const token = localStorage.getItem("token");
    if (!token) {
        alert("Vous devez être connecté.");
        window.location.href = "index.html";
        return;
    }

    const product = document.getElementById('product').value.trim();
    const quantity = parseInt(document.getElementById('quantity').value.trim(), 10);

    if (!product || quantity <= 0) {
        alert("Veuillez entrer un produit valide et une quantité supérieure à 0.");
        return;
    }

    try {
        const response = await fetch("http://localhost/projapi/orders.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + token
            },
            body: JSON.stringify({ product, quantity })
        });

        const data = await response.json();

        if (data.success) {
            alert("Commande ajoutée !");
            loadOrders(); // Rafraîchir la liste des commandes
        } else {
            alert("Erreur : " + data.error);
        }
    } catch (error) {
        console.error("Erreur lors de l'ajout de la commande:", error);
    }
});

// Supprimer une commande via l'API
async function deleteOrder(orderId) {
    const token = localStorage.getItem("token");

    if (!token) {
        alert("Vous devez être connecté.");
        window.location.href = "index.html";
        return;
    }

    try {
        const response = await fetch("http://localhost/projapi/orders.php", {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + token
            },
            body: JSON.stringify({ order_id: orderId })
        });

        const data = await response.json();

        if (data.success) {
            alert("Commande supprimée !");
            loadOrders(); // Rafraîchir la liste
        } else {
            alert("Erreur : " + data.error);
        }
    } catch (error) {
        console.error("Erreur lors de la suppression de la commande:", error);
    }
}

// Mettre à jour une commande
async function updateOrder(orderId) {
    const token = localStorage.getItem("token");

    if (!token) {
        alert("Vous devez être connecté.");
        window.location.href = "index.html";
        return;
    }

    const newProduct = prompt("Entrez le nouveau produit (laissez vide pour ne pas modifier) :");
    const newQuantity = prompt("Entrez la nouvelle quantité (laissez vide pour ne pas modifier) :");

    const updateData = { order_id: orderId };
    if (newProduct.trim() !== "") updateData.product = newProduct.trim();
    if (newQuantity.trim() !== "" && parseInt(newQuantity) > 0) updateData.quantity = parseInt(newQuantity);

    try {
        const response = await fetch("http://localhost/projapi/orders.php", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + token
            },
            body: JSON.stringify(updateData)
        });

        const data = await response.json();

        if (data.success) {
            alert("Commande mise à jour !");
            loadOrders(); // Rafraîchir la liste des commandes
        } else {
            alert("Erreur : " + data.error);
        }
    } catch (error) {
        console.error("Erreur lors de la mise à jour de la commande:", error);
    }
}

// Modifier l'affichage des commandes pour inclure un bouton de modification
function displayOrders(orders) {
    const ordersList = document.getElementById('ordersList');
    ordersList.innerHTML = '';

    orders.forEach(order => {
        const li = document.createElement('li');
        li.classList.add('order-item');
        li.innerHTML = `
            <span>${order.product} (Quantité: ${order.quantity})</span>
            <button class="btn edit-btn" onclick="updateOrder(${order.id})">Modifier</button>
            <button class="btn delete-btn" onclick="deleteOrder(${order.id})">Supprimer</button>
        `;
        ordersList.appendChild(li);
    });
}
