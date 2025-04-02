<?php
// PHP API Handling Section (Top of File)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Replace with your actual API key (store securely in production)
    $apiKey = '   AIzaSyBNOegkMPfYcC_4Wh49UDe0WDlkMe7dASI    '; // Never commit real keys to code
    
    if ($_POST['action'] === 'sendMessage') {
        try {
            $message = $_POST['message'] ?? '';
            
            if (empty($message)) {
                throw new Exception('Message cannot be empty');
            }
            
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;
            
            $postData = [
                'contents' => [
                    'parts' => [
                        ['text' => $message]
                    ]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                throw new Exception('API request failed: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('API returned error: ' . $response);
            }
            
            $responseData = json_decode($response, true);
            $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'I couldn\'t generate a response.';
            
            echo json_encode([
                'success' => true,
                'response' => $aiResponse,
                'timestamp' => date('g:i A')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepSeek Chat</title>
    <style>
        :root {
            --primary-color: #6e48aa;
            --secondary-color: #9d50bb;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
        }

        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            text-align: center;
        }

        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: var(--border-radius);
            position: relative;
            line-height: 1.5;
            animation: fadeIn 0.3s ease-out;
        }

        .user-message {
            align-self: flex-end;
            background-color: var(--primary-color);
            color: white;
            border-bottom-right-radius: 0;
        }

        .ai-message {
            align-self: flex-start;
            background-color: var(--light-color);
            border: 1px solid #e9ecef;
            border-bottom-left-radius: 0;
            box-shadow: var(--shadow);
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
            display: block;
        }

        .input-area {
            padding: 15px;
            background-color: white;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            resize: none;
            font-size: 1rem;
            min-height: 50px;
            max-height: 150px;
            outline: none;
            transition: border-color 0.3s;
        }

        .message-input:focus {
            border-color: var(--primary-color);
        }

        .send-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .send-btn:hover {
            opacity: 0.9;
        }

        .send-btn:disabled {
            background: #ced4da;
            cursor: not-allowed;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 10px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            align-self: flex-start;
            margin-bottom: 15px;
            width: fit-content;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #6c757d;
            border-radius: 50%;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }

        @keyframes typingAnimation {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .chat-messages {
                height: 400px;
                padding: 15px;
            }
            
            .message {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>DeepSeek Chat</h1>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="ai-message">
                Hello! I'm your AI assistant. How can I help you today?
                <span class="message-time"><?= date('g:i A') ?></span>
            </div>
        </div>
        
        <div class="input-area">
            <textarea class="message-input" id="messageInput" placeholder="Type your message here..." rows="1"></textarea>
            <button class="send-btn" id="sendBtn" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            const messageInput = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendBtn');
            
            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
                sendBtn.disabled = this.value.trim() === '';
            });
            
            // Send message on Enter (but allow Shift+Enter for new lines)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!sendBtn.disabled) {
                        sendMessage();
                    }
                }
            });
            
            // Send message on button click
            sendBtn.addEventListener('click', sendMessage);
            
            async function sendMessage() {
                const messageText = messageInput.value.trim();
                if (messageText === '') return;
                
                // Add user message to chat
                addMessage(messageText, 'user');
                messageInput.value = '';
                messageInput.style.height = 'auto';
                sendBtn.disabled = true;
                
                // Show typing indicator
                showTypingIndicator();
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'sendMessage');
                    formData.append('message', messageText);
                    
                    const response = await fetch('<?= $_SERVER['PHP_SELF'] ?>', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        addMessage(data.response, 'ai', data.timestamp);
                    } else {
                        throw new Error(data.error || 'Unknown error');
                    }
                } catch (error) {
                    addMessage("Sorry, I couldn't process your request. Please try again.", 'ai');
                    console.error('Error:', error);
                } finally {
                    removeTypingIndicator();
                }
            }
            
            function addMessage(text, sender, timestamp = null) {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.classList.add(sender === 'user' ? 'user-message' : 'ai-message');
                
                const time = timestamp || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                messageDiv.innerHTML = `
                    ${text}
                    <span class="message-time">${time}</span>
                `;
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.classList.add('typing-indicator');
                typingDiv.id = 'typingIndicator';
                
                typingDiv.innerHTML = `
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                `;
                
                chatMessages.appendChild(typingDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            function removeTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }
        });
    </script>
</body>
</html>
