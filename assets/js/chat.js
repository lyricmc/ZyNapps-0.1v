// Chat functionality with real-time updates

let chatInterval
let lastMessageId = null

// Initialize chat
document.addEventListener("DOMContentLoaded", () => {
  const chatForm = document.getElementById("chatForm")
  const chatMessages = document.getElementById("chatMessages")

  if (chatForm) {
    chatForm.addEventListener("submit", sendMessage)

    // Start polling for new messages
    const urlParams = new URLSearchParams(window.location.search)
    const chatWith = urlParams.get("with")

    if (chatWith) {
      startMessagePolling(chatWith)
      scrollToBottom()
    }
  }
})

// Send message
async function sendMessage(event) {
  event.preventDefault()

  const form = event.target
  const formData = new FormData(form)

  try {
    const response = await fetch("api/send_message.php", {
      method: "POST",
      body: formData,
    })

    const data = await response.json()

    if (data.success) {
      form.querySelector('input[name="message"]').value = ""
      addMessageToChat(data.message, true)
      scrollToBottom()
    } else {
      alert(data.error || "Error al enviar mensaje")
    }
  } catch (error) {
    console.error("Error sending message:", error)
  }
}

// Add message to chat interface
function addMessageToChat(message, isSent = false) {
  const chatMessages = document.getElementById("chatMessages")
  const messageDiv = document.createElement("div")
  messageDiv.className = `message ${isSent ? "sent" : "received"}`

  let content = ""
  if (message.file && message.type === "image") {
    content += `<img src="assets/uploads/chat/${message.file}" alt="Image" class="chat-image">`
  }
  if (message.message) {
    content += `<p>${message.message.replace(/\n/g, "<br>")}</p>`
  }

  messageDiv.innerHTML = `
        <div class="message-content">
            ${content}
            <span class="message-time">${formatTime(message.timestamp)}</span>
        </div>
    `

  chatMessages.appendChild(messageDiv)
  lastMessageId = message.id
}

// Start polling for new messages
function startMessagePolling(chatWith) {
  chatInterval = setInterval(() => {
    fetchNewMessages(chatWith)
  }, 2000) // Check every 2 seconds
}

// Fetch new messages
async function fetchNewMessages(chatWith) {
  try {
    const response = await fetch(`api/get_messages.php?with=${chatWith}&after=${lastMessageId || ""}`)
    const data = await response.json()

    if (data.success && data.messages.length > 0) {
      data.messages.forEach((message) => {
        if (!lastMessageId || message.id !== lastMessageId) {
          const isSent = message.from === getCurrentUser()
          addMessageToChat(message, isSent)
        }
      })
      scrollToBottom()
    }
  } catch (error) {
    console.error("Error fetching messages:", error)
  }
}

// Scroll to bottom of chat
function scrollToBottom() {
  const chatMessages = document.getElementById("chatMessages")
  if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight
  }
}

// Format timestamp
function formatTime(timestamp) {
  const date = new Date(timestamp * 1000)
  return date.toLocaleTimeString("es-ES", {
    hour: "2-digit",
    minute: "2-digit",
  })
}

// Get current user (this would be set from PHP)
function getCurrentUser() {
  // This should be set from PHP session
  return window.currentUser || ""
}

// Handle file upload in chat
document.addEventListener("change", (event) => {
  if (event.target.id === "fileInput") {
    const file = event.target.files[0]
    if (file) {
      // Create FormData and send file
      const formData = new FormData()
      formData.append("file", file)
      formData.append("to", new URLSearchParams(window.location.search).get("with"))
      formData.append("message", "") // Empty message for file-only

      fetch("api/send_message.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            addMessageToChat(data.message, true)
            scrollToBottom()
          }
        })
        .catch((error) => {
          console.error("Error sending file:", error)
        })
    }
  }
})

// Clean up interval when leaving page
window.addEventListener("beforeunload", () => {
  if (chatInterval) {
    clearInterval(chatInterval)
  }
})
