// Profile page functionality

// Toggle edit profile form
function toggleEditProfile() {
  const form = document.getElementById("editProfileForm")
  if (form.style.display === "none" || !form.style.display) {
    form.style.display = "block"
  } else {
    form.style.display = "none"
  }
}

// Preview profile picture before upload
document.addEventListener("change", (event) => {
  if (event.target.name === "profile_pic") {
    const file = event.target.files[0]
    if (file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        const profilePic = document.querySelector(".profile-pic-large")
        if (profilePic) {
          profilePic.src = e.target.result
        }
      }
      reader.readAsDataURL(file)
    }
  }
})

// Open post in modal/lightbox
function openPost(postId) {
  // Create modal overlay
  const modal = document.createElement("div")
  modal.className = "post-modal"
  modal.innerHTML = `
        <div class="modal-overlay" onclick="closePostModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <button class="close-btn" onclick="closePostModal()">×</button>
                <div id="modalPostContent">Cargando...</div>
            </div>
        </div>
    `

  // Add modal styles
  const style = document.createElement("style")
  style.textContent = `
        .post-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2000;
        }
        .modal-overlay {
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            z-index: 2001;
        }
    `

  document.head.appendChild(style)
  document.body.appendChild(modal)

  // Fetch post details
  fetchPostDetails(postId)
}

// Close post modal
function closePostModal() {
  const modal = document.querySelector(".post-modal")
  if (modal) {
    modal.remove()
  }
}

// Fetch post details for modal
async function fetchPostDetails(postId) {
  try {
    const response = await fetch(`api/get_post.php?id=${postId}`)
    const data = await response.json()

    if (data.success) {
      const modalContent = document.getElementById("modalPostContent")
      const post = data.post

      let mediaHtml = ""
      if (post.file) {
        if (post.type === "image") {
          mediaHtml = `<img src="assets/uploads/${post.file}" alt="Post image" style="width: 100%; border-radius: 8px;">`
        } else {
          mediaHtml = `<video controls style="width: 100%; border-radius: 8px;">
                        <source src="assets/uploads/${post.file}" type="video/mp4">
                    </video>`
        }
      }

      modalContent.innerHTML = `
                <div class="post" style="border: none; padding: 2rem;">
                    <div class="post-header">
                        <div class="user-info">
                            <img src="assets/uploads/profiles/${post.user_profile_pic || "default.jpg"}" 
                                 alt="Profile" class="profile-pic-small">
                            <span class="username">${post.user}</span>
                        </div>
                        <span class="timestamp">${post.time_ago}</span>
                    </div>
                    
                    ${post.description ? `<div class="post-description">${post.description.replace(/\n/g, "<br>")}</div>` : ""}
                    
                    ${mediaHtml ? `<div class="post-media">${mediaHtml}</div>` : ""}
                    
                    <div class="post-actions">
                        <button class="like-btn ${post.user_liked ? "liked" : ""}" 
                                onclick="toggleLike('${post.id}')">
                            ❤️ <span class="like-count">${post.like_count}</span>
                        </button>
                        <button class="comment-btn">
                            💬 ${post.comment_count} comentarios
                        </button>
                    </div>
                    
                    <div class="comments-section" style="display: block;">
                        <div class="comments-list">
                            ${post.comments
                              .map(
                                (comment) => `
                                <div class="comment">
                                    <strong>${comment.user}:</strong>
                                    ${comment.text.replace(/\n/g, "<br>")}
                                    <span class="comment-time">${comment.time_ago}</span>
                                </div>
                            `,
                              )
                              .join("")}
                        </div>
                        <form class="comment-form" onsubmit="addComment(event, '${post.id}')">
                            <input type="text" placeholder="Escribe un comentario..." required>
                            <button type="submit">Enviar</button>
                        </form>
                    </div>
                </div>
            `
    }
  } catch (error) {
    console.error("Error fetching post details:", error)
    document.getElementById("modalPostContent").innerHTML = "<p>Error al cargar el post</p>"
  }
}

// Handle keyboard navigation
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closePostModal()
  }
})
