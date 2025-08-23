// Friends page functionality

// Show different tabs
function showTab(tabName) {
  // Hide all tab contents
  const tabContents = document.querySelectorAll(".tab-content")
  tabContents.forEach((content) => {
    content.classList.remove("active")
  })

  // Remove active class from all tab buttons
  const tabBtns = document.querySelectorAll(".tab-btn")
  tabBtns.forEach((btn) => {
    btn.classList.remove("active")
  })

  // Show selected tab content
  document.getElementById(tabName).classList.add("active")

  // Add active class to clicked button
  event.target.classList.add("active")
}

// Search users
let searchTimeout
async function searchUsers() {
  const query = document.getElementById("searchInput").value.trim()

  // Clear previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  // Debounce search
  searchTimeout = setTimeout(async () => {
    if (query.length < 2) {
      document.getElementById("searchResults").innerHTML = ""
      return
    }

    try {
      const response = await fetch(`api/search_users.php?q=${encodeURIComponent(query)}`)
      const data = await response.json()

      if (data.success) {
        displaySearchResults(data.users)
      }
    } catch (error) {
      console.error("Error searching users:", error)
    }
  }, 300)
}

// Display search results
function displaySearchResults(users) {
  const resultsContainer = document.getElementById("searchResults")

  if (users.length === 0) {
    resultsContainer.innerHTML = "<p>No se encontraron usuarios</p>"
    return
  }

  let html = ""
  users.forEach((user) => {
    html += `
            <div class="friend-card">
                <img src="assets/uploads/profiles/${user.profile_pic || "default.jpg"}" 
                     alt="Profile" class="profile-pic-medium">
                <h4>${user.full_name}</h4>
                <p>@${user.username}</p>
                <div class="friend-actions">
                    <button onclick="location.href='profile.php?user=${user.username}'" class="btn-secondary">Ver perfil</button>
                    <button onclick="sendFriendRequest('${user.username}')" class="btn-primary">Agregar</button>
                </div>
            </div>
        `
  })

  resultsContainer.innerHTML = html
}

// Send friend request (reuse from main.js)
async function sendFriendRequest(username) {
  try {
    const response = await fetch("api/friend_request.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=send&to=${username}`,
    })

    const data = await response.json()

    if (data.success) {
      alert("Solicitud de amistad enviada")
      // Update UI to show request sent
      event.target.textContent = "Enviada"
      event.target.disabled = true
    } else {
      alert(data.error || "Error al enviar solicitud")
    }
  } catch (error) {
    console.error("Error sending friend request:", error)
  }
}

// Accept friend request
async function acceptFriendRequest(username) {
  try {
    const response = await fetch("api/friend_request.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=accept&from=${username}`,
    })

    const data = await response.json()

    if (data.success) {
      alert("Solicitud aceptada")
      location.reload()
    } else {
      alert(data.error || "Error al aceptar solicitud")
    }
  } catch (error) {
    console.error("Error accepting friend request:", error)
  }
}

// Reject friend request
async function rejectFriendRequest(username) {
  try {
    const response = await fetch("api/friend_request.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=reject&from=${username}`,
    })

    const data = await response.json()

    if (data.success) {
      alert("Solicitud rechazada")
      location.reload()
    } else {
      alert(data.error || "Error al rechazar solicitud")
    }
  } catch (error) {
    console.error("Error rejecting friend request:", error)
  }
}
