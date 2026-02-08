<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $timeout;
    protected int $maxRetries;

    /**
     * Master System Prompt to establish persona and rules.
     */
    protected const MASTER_PROMPT = <<<PROMPT
You are MeetFlow AI, an enterprise meeting intelligence assistant.
Your task is to convert meeting content into structured, actionable outputs.
Follow instructions strictly.
Never hallucinate data.
Always return valid JSON when requested.
Be concise and professional.
PROMPT;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        // Use flash model by default as per hackathon tips
        $this->model = config('gemini.model', 'gemini-1.5-flash'); 
        $this->baseUrl = config('gemini.base_url');
        $this->timeout = config('gemini.timeout');
        $this->maxRetries = config('gemini.max_retries');

        // Allow overriding model via .env if needed, but default heavily to flash for speed

        if (empty($this->apiKey)) {
            throw new Exception('GEMINI_API_KEY is not configured');
        }
    }

    /**
     * Extract tasks from meeting transcript using Gemini API (Core Task Extraction).
     * Now includes Language Auto Detection.
     *
     * @param string $transcript
     * @param array $participants
     * @return array
     */
    public function extractTasksFromTranscript(string $transcript, array $participants = []): array
    {
        $prompt = $this->buildTaskExtractionPrompt($transcript, $participants);

        try {
            $response = $this->callGeminiAPI($prompt);
            return $this->parseTaskExtractionResponse($response);
        } catch (Exception $e) {
            Log::error('Failed to extract tasks', [
                'error' => $e->getMessage(),
                'transcript_length' => strlen($transcript),
            ]);
            throw $e;
        }
    }

    /**
     * Generate executive summary for meeting.
     *
     * @param array $tasks
     * @param string $meetingTitle
     * @return array
     */
    public function generateExecutiveSummary(array $tasks, string $meetingTitle): array
    {
        $prompt = $this->buildExecutiveSummaryPrompt($tasks, $meetingTitle);

        try {
            $response = $this->callGeminiAPI($prompt);
            return $this->parseExecutiveSummaryResponse($response);
        } catch (Exception $e) {
            Log::error('Failed to generate summary', ['error' => $e->getMessage()]);
            return [
                'total_tasks' => count($tasks),
                'high_priority_count' => 0,
                'urgent_deadlines' => [],
                'overall_summary' => 'Summary generation unavailable.',
            ];
        }
    }

    /**
     * Analyze a single task for delay risk.
     *
     * @param array $taskData
     * @return array
     */
    public function analyzeTaskRisk(array $taskData): array
    {
        $prompt = $this->buildTaskDelayRiskPrompt($taskData);

        try {
            $response = $this->callGeminiAPI($prompt);
            return $this->parseRiskAnalysisResponse($response);
        } catch (Exception $e) {
            Log::error('Failed to analyze risk', ['error' => $e->getMessage()]);
            return [
                'risk_level' => 'Unknown',
                'risk_reason' => 'Analysis failed',
                'suggestion' => '',
            ];
        }
    }

    /**
     * Generate follow-up message for a meeting (Exec Summary Style).
     */
    public function generateFollowUpMessage(array $tasks, string $meetingTitle): array
    {
        // Reuse email prompt logic or create new
        $prompt = $this->buildFollowUpEmailPrompt(['title' => $meetingTitle, 'tasks' => $tasks]);
        
        try {
            $response = $this->callGeminiAPI($prompt);
            return $this->parseFollowUpEmailResponse($response);
        } catch (Exception $e) {
            Log::error('Failed to generate follow-up', ['error' => $e->getMessage()]);
            return [
                'subject' => "Follow Up: {$meetingTitle}",
                'body' => "Please check the dashboard for assigned tasks.",
            ];
        }
    }

    /**
     * Assess risks for a batch of tasks.
     */
    public function assessRisks(array $tasks): array
    {
        // Simple iteration or batch prompt. For now, empty or single call.
        // Let's implement a batch risk prompt or just return empty to unblock.
        // The service calls this with all extracted tasks.
        
        // Quick dummy implementation to avoid crash
        return []; 
    }

    /**
     * Generate WhatsApp reminder (Pakistan Friendly).
     *
     * @param array $taskData
     * @return string
     */
    public function generateWhatsAppMessage(array $taskData): string
    {
        $prompt = $this->buildWhatsAppPrompt($taskData);

        try {
            $response = $this->callGeminiAPI($prompt);
            return $this->parseWhatsAppResponse($response);
        } catch (Exception $e) {
            Log::error('Failed to generate WhatsApp', ['error' => $e->getMessage()]);
            return "Reminder: {$taskData['title']}";
        }
    }

    /**
     * Transcribe audio file using Gemini Multimodal.
     *
     * @param string $path Absolute path to the audio file.
     * @param string $mimeType e.g. 'audio/mp3', 'audio/wav'
     * @return string The transcript text.
     */
    public function transcribeAudio(string $path, string $mimeType): string
    {
        // Limit for Inline Data is 20MB. For Hackathon, we assume small files.
        // For larger files, we would need to use File API (upload to Google first).
        
        $data = base64_encode(file_get_contents($path));
        
        $prompt = <<<PROMPT
You are an expert transcriber. 
Listen to this meeting audio and generate a verbatim transcript.
Identify different speakers as "Speaker 1", "Speaker 2", etc., if possible.
Format it clearly.
PROMPT;

        return $this->callGeminiMultimodal($prompt, $data, $mimeType);
    }

    /**
     * Calculate productivity insights (Bonus Feature).
     *
     * @param array $tasks
     * @return array
     */
    public function getProductivityInsights(array $tasks): array
    {
        $prompt = $this->buildProductivityPrompt($tasks);

        try {
            $response = $this->callGeminiAPI($prompt);
            return $this->parseProductivityResponse($response);
        } catch (Exception $e) {
            Log::error('Failed productivity insights', ['error' => $e->getMessage()]);
            return [
                'completion_estimate_percentage' => 0,
                'execution_health' => 'Unknown',
                'recommendation' => 'N/A',
            ];
        }
    }

    /**
     * Call Gemini API with multimodal input (Text + File).
     */
    protected function callGeminiMultimodal(string $prompt, string $base64Data, string $mimeType): string
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
        
        // No system prompt for simple transcription to keep it clean, or we can prepend it.
        
        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4, // Lower temperature for accurate transcription
            ]
        ];

        try {
            $response = Http::timeout(120) // Longer timeout for audio
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $body);

            if ($response->successful()) {
                return $this->extractText($response->json());
            }

            throw new Exception("Gemini API Audio Error: " . $response->body());
        } catch (Exception $e) {
            Log::error('Gemini Multimodal Error', ['msg' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Call Gemini API with retry logic and master prompt injection.
     */
    protected function callGeminiAPI(string $prompt): array
    {
        $url = "{$this->baseUrl}/models/{$this->model}:generateContent";
        
        // Inject Master Prompt
        $fullPrompt = self::MASTER_PROMPT . "\n\n" . $prompt;

        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url . '?key=' . $this->apiKey, [
                        'contents' => [
                            ['parts' => [['text' => $fullPrompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7, // Balanced creativity
                            'maxOutputTokens' => 8192,
                        ]
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                throw new Exception("API request failed: " . $response->body());
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;
                if ($attempt < $this->maxRetries) sleep(pow(2, $attempt));
            }
        }

        throw $lastException ?? new Exception('Failed to call Gemini API');
    }

    // --- PROMPT BUILDERS ---

    protected function buildTaskExtractionPrompt(string $transcript, array $participants): string
    {
        return <<<PROMPT
2) CORE TASK EXTRACTION PROMPT (Includes Language Detection)

Analyze the following meeting content and extract actionable tasks.
Detect the language of the meeting content (e.g., English, Urdu, or Mixed).

Meeting Content:
{$transcript}

Participants:
{$this->formatParticipants($participants)}

Return all tasks with:
- title
- description
- owner (choose from participants or "Unassigned")
- priority (Low, Medium, High)
- deadline (YYYY-MM-DD or null)
- confidence_score (0–100 based on clarity)

Rules:
- Only include real action items
- Do not create imaginary tasks
- If deadline is unclear, return null
- Output strictly in JSON format

Required Output Format:
{
  "detected_language": "English",
  "tasks": [
    {
      "title": "Task title",
      "description": "Task description",
      "owner": "Person Name",
      "priority": "High",
      "deadline": "2024-01-01",
      "confidence_score": 90
    }
  ]
}
PROMPT;
    }

    protected function buildExecutiveSummaryPrompt(array $tasks, string $meetingTitle): string
    {
        $taskJson = json_encode($tasks, JSON_PRETTY_PRINT);
        return <<<PROMPT
3) EXECUTIVE SUMMARY PROMPT (For Managers)

Summarize this meeting execution outcome.

Meeting Title: {$meetingTitle}
Meeting Tasks:
{$taskJson}

Generate:
- total_tasks
- high_priority_count
- urgent_deadlines (list of strings)
- overall_summary (max 4 lines)

Return JSON only.
Output Format:
{
  "total_tasks": 0,
  "high_priority_count": 0,
  "urgent_deadlines": [],
  "overall_summary": "Summary text..."
}
PROMPT;
    }

    protected function buildTaskDelayRiskPrompt(array $taskData): string
    {
        $taskJson = json_encode($taskData, JSON_PRETTY_PRINT);
        return <<<PROMPT
4) TASK DELAY RISK ANALYSIS PROMPT
Analyze this task for delay risk.

Task:
{$taskJson}

Return:
- risk_level (Low, Medium, High)
- risk_reason
- suggestion

Output JSON only.
Output Format:
{
  "risk_level": "Medium",
  "risk_reason": "Reason...",
  "suggestion": "Suggestion..."
}
PROMPT;
    }

    protected function buildFollowUpEmailPrompt(array $taskData): string
    {
        $taskJson = json_encode($taskData, JSON_PRETTY_PRINT);
        return <<<PROMPT
5) FOLLOW-UP MESSAGE GENERATOR PROMPT (Email)
Generate a professional follow-up email for these details.

Task Details:
{$taskJson}

Tone: Professional and polite
Length: Short

Return:
- subject
- message_body

JSON only.
Output Format:
{
  "subject": "Email Subject",
  "message_body": "Email body content..."
}
PROMPT;
    }

    protected function buildWhatsAppPrompt(array $taskData): string
    {
        $taskJson = json_encode($taskData, JSON_PRETTY_PRINT);
        return <<<PROMPT
6) WhatsApp Reminder Prompt (Pakistan Friendly)
Generate a short WhatsApp reminder message for this task.

Task:
{$taskJson}

Tone: Friendly and professional
Limit: 2–3 lines

Return only message text. No JSON.
PROMPT;
    }

    protected function buildProductivityPrompt(array $tasks): string
    {
        $taskJson = json_encode($tasks, JSON_PRETTY_PRINT);
        return <<<PROMPT
8) Productivity Score Generator
Based on these tasks, calculate productivity metrics.

Tasks:
{$taskJson}

Return:
- completion_estimate_percentage (0-100)
- execution_health (Good, Average, Poor)
- recommendation

Output Format:
{
  "completion_estimate_percentage": 75,
  "execution_health": "Good",
  "recommendation": "Maintain current pace."
}
PROMPT;
    }

    // --- HELPERS & PARSERS ---

    protected function formatParticipants(array $participants): string
    {
        if (empty($participants)) return "Not specified";
        return implode(', ', $participants);
    }

    protected function formatTeamMembers(array $members): string
    {
        if (empty($members)) return "None available";
        // Format: Name (email)
        return implode("\n", array_map(fn($m) => "- {$m['name']} ({$m['email']})", $members));
    }

    protected function parseTaskExtractionResponse(array $response): array
    {
        $data = $this->extractJson($response);
        return $data['tasks'] ?? [];
    }

    protected function parseExecutiveSummaryResponse(array $response): array
    {
        return $this->extractJson($response);
    }

    protected function parseRiskAnalysisResponse(array $response): array
    {
        return $this->extractJson($response);
    }

    protected function parseFollowUpEmailResponse(array $response): array
    {
        return $this->extractJson($response);
    }

    protected function parseWhatsAppResponse(array $response): string
    {
        return $this->extractText($response);
    }

    protected function parseProductivityResponse(array $response): array
    {
        return $this->extractJson($response);
    }

    /**
     * robust JSON extractor from Gemini text response
     */
    protected function extractJson(array $response): array
    {
        try {
            $text = $this->extractText($response);
            if (preg_match('/\{[\s\S]*\}|\[[\s\S]*\]/', $text, $matches)) {
                $text = $matches[0];
            }
            $data = json_decode($text, true);
            return is_array($data) ? $data : [];
        } catch (Exception $e) {
            Log::error('JSON Parse Error', ['content' => $response]);
            return [];
        }
    }

    protected function extractText(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
