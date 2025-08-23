// Main JavaScript functionality

// Toggle user menu
function toggleUserMenu() {
  const menu = document.getElementById("userMenu")
  menu.classList.toggle("show")
}

// Close menu when clicking outside
document.addEventListener("click", (event) => {
  const menu = document.getElementById("userMenu")
  const menuBtn = document.querySelector(".menu-btn")

  if (!menu.contains(event.target) && !menuBtn.contains(event.target)) {
    menu.classList.remove("show")
  }
})

// Toggle like on post
async function toggleLike(postId) {
  try {
    const response = await fetch("api/toggle_like.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `post_id=${postId}`,
    })

    const data = await response.json()

    if (data.success) {
      const likeBtn = document.querySelector(`[data-post-id="${postId}"] .like-btn`)
      const likeCount = likeBtn.querySelector(".like-count")

      if (data.liked) {
        likeBtn.classList.add("liked")
      } else {
        likeBtn.classList.remove("liked")
      }

      likeCount.textContent = data.count
    }
  } catch (error) {
    console.error("Error toggling like:", error)
  }
}

// Toggle comments section
function toggleComments(postId) {
  const commentsSection = document.getElementById(`comments-${postId}`)
  if (commentsSection.style.display === "none") {
    commentsSection.style.display = "block"
  } else {
    commentsSection.style.display = "none"
  }
}

// Add comment to post
async function addComment(event, postId) {
  event.preventDefault()

  const form = event.target
  const input = form.querySelector('input[type="text"]')
  const text = input.value.trim()

  if (!text) return

  try {
    const response = await fetch("api/add_comment.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `post_id=${postId}&text=${encodeURIComponent(text)}`,
    })

    const data = await response.json()

    if (data.success) {
      const commentsList = document.querySelector(`#comments-${postId} .comments-list`)
      const commentDiv = document.createElement("div")
      commentDiv.className = "comment"
      commentDiv.innerHTML = `
                <strong>${data.comment.user}:</strong>
                ${data.comment.text.replace(/\n/g, "<br>")}
                <span class="comment-time">ahora</span>
            `
      commentsList.appendChild(commentDiv)
      input.value = ""
    }
  } catch (error) {
    console.error("Error adding comment:", error)
  }
}

// Send friend request
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
      location.reload()
    } else {
      alert(data.error || "Error al enviar solicitud")
    }
  } catch (error) {
    console.error("Error sending friend request:", error)
  }
}

// Remove friend
async function removeFriend(username) {
  if (!confirm("¿Estás seguro de que quieres eliminar a este amigo?")) {
    return
  }

  try {
    const response = await fetch("api/friend_request.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=remove&user=${username}`,
    })

    const data = await response.json()

    if (data.success) {
      alert("Amigo eliminado")
      location.reload()
    } else {
      alert(data.error || "Error al eliminar amigo")
    }
  } catch (error) {
    console.error("Error removing friend:", error)
  }
}

// Open chat with user
function openChat(username) {
  window.location.href = `chat.php?with=${username}`
}

// Open post in modal (placeholder)
function openPost(postId) {
  // This would open a modal with the full post
  console.log("Opening post:", postId)
}

// Auto-refresh feed every 30 seconds
setInterval(() => {
  // Only refresh if user is on the main page and not actively typing
  if (window.location.pathname.endsWith("index.php") || window.location.pathname === "/") {
    const activeElement = document.activeElement
    if (activeElement.tagName !== "INPUT" && activeElement.tagName !== "TEXTAREA") {
      // Subtle refresh without full page reload
      // This would be implemented with AJAX to fetch new posts
    }
  }
}, 30000)

// Image preview for file uploads
document.addEventListener("change", (event) => {
  if (event.target.type === "file" && event.target.accept && event.target.accept.includes("image")) {
    const file = event.target.files[0]
    if (file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        // Create or update image preview
        let preview = event.target.parentNode.querySelector(".image-preview")
        if (!preview) {
          preview = document.createElement("img")
          preview.className = "image-preview"
          preview.style.maxWidth = "200px"
          preview.style.marginTop = "10px"
          preview.style.borderRadius = "8px"
          event.target.parentNode.appendChild(preview)
        }
        preview.src = e.target.result
      }
      reader.readAsDataURL(file)
    }
  }
})
