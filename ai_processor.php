<?php
require_once 'config.php';

class OfflineAIChat {
    private $pdo;
    private $userId;
    private $conversationId;
    private $conversationHistory = [];
    private $userProfile = [];
    
    // Conversation states and memory
    private $currentTopic = '';
    private $lastExercise = '';
    private $lastMentionedDate = '';
    private $userPreferences = [];
    
    public function __construct($pdo, $userId, $conversationId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->conversationId = $conversationId;
        $this->loadUserProfile();
        $this->loadConversationHistory();
    }
    
    private function loadUserProfile() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $this->userProfile = $stmt->fetch() ?: [];
    }
    
    private function loadConversationHistory() {
        $stmt = $this->pdo->prepare("SELECT * FROM ai_chat_messages 
                                    WHERE conversation_id = ? 
                                    ORDER BY sent_at ASC");
        $stmt->execute([$this->conversationId]);
        $this->conversationHistory = $stmt->fetchAll();
    }
    
    public function processMessage($userMessage) {
        // Analyze message and update conversation state
        $this->analyzeMessage($userMessage);
        
        // Check for emergency situations first
        if ($response = $this->checkEmergency($userMessage)) {
            return $response;
        }
        
        // Maintain conversation flow
        if ($followUp = $this->checkFollowUp()) {
            return $followUp;
        }
        
        // Handle specific topics
        if ($response = $this->handleTopics($userMessage)) {
            return $response;
        }
        
        // Default response if nothing else matches
        return $this->generateDefaultResponse();
    }
    
    private function analyzeMessage($message) {
        $messageLower = strtolower($message);
        
        // Detect topic changes
        if (preg_match('/exercise|workout|physical/i', $messageLower)) {
            $this->currentTopic = 'exercise';
        } 
        elseif (preg_match('/speech|talk|word|language/i', $messageLower)) {
            $this->currentTopic = 'speech';
        }
        elseif (preg_match('/food|eat|nutrition|diet/i', $messageLower)) {
            $this->currentTopic = 'nutrition';
        }
        elseif (preg_match('/pain|hurt|ache|discomfort/i', $messageLower)) {
            $this->currentTopic = 'pain';
        }
        
        // Extract exercise names if mentioned
        if ($this->currentTopic == 'exercise' && 
            preg_match('/arm|leg|shoulder|hand|foot/i', $messageLower, $matches)) {
            $this->lastExercise = $matches[0];
        }
        
        // Extract dates if mentioned
        if (preg_match('/(today|yesterday|tomorrow|monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i', $messageLower, $matches)) {
            $this->lastMentionedDate = $matches[0];
        }
    }
    
    private function checkEmergency($message) {
        $emergencyKeywords = [
            'chest pain', 'can\'t breathe', 'severe headache', 
            'numb', 'vision loss', 'unconscious'
        ];
        
        foreach ($emergencyKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return "This sounds serious! Please call emergency services immediately. " . 
                       "I've notified your care team about this conversation.";
            }
        }
        return false;
    }
    
    private function checkFollowUp() {
        // Continue current topic if relevant
        if ($this->currentTopic == 'exercise' && $this->lastExercise) {
            return "How is your " . $this->lastExercise . " feeling after the exercises we discussed?";
        }
        
        // Follow up on pain reports
        if ($this->currentTopic == 'pain') {
            return "On a scale from 1 to 10, how would you rate your pain now?";
        }
        
        return false;
    }
    
    private function handleTopics($message) {
        $messageLower = strtolower($message);
        
        // Exercise-related responses
        if ($this->currentTopic == 'exercise') {
            $exercises = [
                'arm' => "Try gentle arm circles: extend your arms sideways and make small circles. Do 10 forward and 10 backward.",
                'leg' => "For leg exercises, try seated marches: sit tall and lift one knee at a time. Aim for 10 per leg.",
                'shoulder' => "Shoulder rolls are great - slowly roll your shoulders forward 10 times, then backward 10 times."
            ];
            
            foreach ($exercises as $key => $response) {
                if (strpos($messageLower, $key) !== false) {
                    return $response . " Would you like me to remind you about this exercise later?";
                }
            }
            
            return "For general exercise, try these: \n" . 
                   "1. Seated marches (10 per leg)\n" . 
                   "2. Shoulder rolls (10 each direction)\n" . 
                   "3. Ankle circles (10 each foot)\n" . 
                   "Which one would you like to try first?";
        }
        
        // Speech-related responses
        if ($this->currentTopic == 'speech') {
            if (strpos($messageLower, 'word') !== false) {
                return "Try practicing with simple words like 'hello', 'water', and 'help'. " . 
                       "Say each word slowly 5 times. How does that feel?";
            }
            
            return "Speech exercises:\n" . 
                   "1. Read aloud for 5 minutes daily\n" . 
                   "2. Practice counting from 1 to 20\n" . 
                   "3. Name objects around you\n" . 
                   "Would you like to try one of these?";
        }
        
        // Nutrition-related responses
        if ($this->currentTopic == 'nutrition') {
            $foods = [
                'breakfast' => "Try oatmeal with berries for breakfast - it's great for brain health!",
                'lunch' => "A salmon salad with leafy greens makes an excellent lunch option.",
                'dinner' => "For dinner, grilled chicken with steamed vegetables is a good choice."
            ];
            
            foreach ($foods as $key => $response) {
                if (strpos($messageLower, $key) !== false) {
                    return $response;
                }
            }
            
            return "Healthy eating tips:\n" . 
                   "1. Focus on colorful fruits and vegetables\n" . 
                   "2. Choose whole grains over refined ones\n" . 
                   "3. Include omega-3 rich foods like fish\n" . 
                   "What meal are you planning next?";
        }
        
        // Pain-related responses
        if ($this->currentTopic == 'pain') {
            if (preg_match('/[0-9]+/', $message, $matches)) {
                $painLevel = $matches[0];
                if ($painLevel > 7) {
                    return "A pain level of $painLevel is concerning. Have you taken your prescribed medication? " . 
                           "Would you like me to notify your doctor?";
                } else {
                    return "For pain at level $painLevel, try:\n" . 
                           "1. Gentle stretching\n" . 
                           "2. Deep breathing exercises\n" . 
                           "3. Applying a warm compress\n" . 
                           "Would you like details on any of these?";
                }
            }
        }
        
        return false;
    }
    
    private function generateDefaultResponse() {
        $defaultResponses = [
            "I understand. Could you tell me more about how you're feeling?",
            "That's interesting. What else would you like to discuss today?",
            "I've made a note of that. Is there anything specific you'd like help with?",
            "Thank you for sharing. Would you like to talk about exercises, nutrition, or something else?"
        ];
        
        // Check last few messages to avoid repetition
        $lastMessages = array_slice($this->conversationHistory, -3);
        foreach ($lastMessages as $msg) {
            if ($msg['sender_role'] == 'ai') {
                foreach ($defaultResponses as $key => $response) {
                    if (strpos($msg['message'], $response) !== false) {
                        unset($defaultResponses[$key]);
                    }
                }
            }
        }
        
        return $defaultResponses[array_rand($defaultResponses)];
    }
}

function processWithAI($userMessage, $conversationHistory, $userId) {
    global $pdo;
    static $ai = null;
    
    if ($ai === null) {
        // Get conversation ID from history
        $conversationId = !empty($conversationHistory) ? $conversationHistory[0]['conversation_id'] : 0;
        $ai = new OfflineAIChat($pdo, $userId, $conversationId);
    }
    
    return $ai->processMessage($userMessage);
}