# MeetFlow AI – Gemini Prompt Engineering & AI Logic Design

## 1. AI Design Philosophy

MeetFlow AI uses Gemini 3 not as a chatbot but as a structured reasoning engine. The AI is instructed to extract actionable information, make priority judgments, infer deadlines, and return clean JSON output that can be directly used by the application.

## 2. Input Types to Gemini

MeetFlow AI accepts the following inputs:
- Meeting transcript text
- Meeting summary text
- Structured context metadata (optional)

## 3. Core Prompt Structure

Each Gemini request consists of:
1. System Instruction
2. Task Definition
3. Output Format Constraint
4. Validation Rules

## 4. System Instruction Prompt

Use this EXACT base prompt:

You are an enterprise meeting analysis assistant.
Your task is to extract actionable tasks and decisions from meeting transcripts.
Be precise, professional, and structured.
Do not invent information.
Return only valid JSON.

## 5. Task Extraction Prompt Template

Analyze the following meeting transcript:

{{MEETING_TEXT}}

Extract all actionable tasks and decisions.

For each task return:

- title (short action title)
- description (what needs to be done)
- owner (person responsible)
- priority (Low, Medium, High)
- deadline (ISO format YYYY-MM-DD or null)
- confidence_score (0-100, how confident you are this is a real task)

Only include tasks that require action.

## 6. Output Schema Definition

Output must strictly follow this JSON format:

```json
{
  "tasks": [
    {
      "title": "",
      "description": "",
      "owner": "",
      "priority": "",
      "deadline": "",
      "confidence_score": 0
    }
  ]
}
```

## 7. Priority Inference Rules

Gemini should infer priority using:
- Urgency of language (ASAP, urgent, immediately)
- Business impact keywords
- Deadline proximity

Rules:
- Critical deadline or urgent → High
- Normal work → Medium
- Optional or future planning → Low

## 8. Deadline Inference Rules

If transcript mentions:
- "by Friday" → Convert to nearest calendar date
- "next week" → Set 7 days ahead
- "end of month" → Last day of current month

If no deadline mentioned:
Return null.

## 9. Error Handling Strategy

If transcript has:
- No clear action items → Return empty tasks array
- Ambiguous owner → Set owner as "Unassigned"
- Missing data → Do not hallucinate

## 10. Follow-Up Message Generator Prompt

### Follow-Up Prompt Template:
Generate a professional follow-up message for this task:

Task Title: {{TITLE}}
Owner: {{OWNER}}
Deadline: {{DEADLINE}}
Priority: {{PRIORITY}}

Tone: Professional and polite.
Length: Short email format.

## 11. Delay Risk Prediction Prompt

### Risk Analysis Prompt:
Analyze this task:

Title: {{TITLE}}
Deadline: {{DEADLINE}}
Priority: {{PRIORITY}}

Predict risk level (Low, Medium, High) and provide short explanation.

## 12. Executive Summary Generator Prompt

Generate an executive summary for this meeting:

Meeting Title: {{TITLE}}
Tasks Extracted: {{TASK_COUNT}}
Urgent Tasks: {{URGENT_COUNT}}

Provide:
- Key decisions made
- Action items overview
- Urgent deadlines
- Team productivity insights

## 13. WhatsApp Follow-Up Generator Prompt

Generate a short WhatsApp reminder message for this task:

Task: {{TITLE}}
Owner: {{OWNER}}
Deadline: {{DEADLINE}}

Format: Short, friendly, professional.
Max 160 characters.

## 14. AI Safety Guidelines

MeetFlow AI must:
- Avoid generating sensitive personal data
- Avoid legal or financial advice
- Provide neutral and professional outputs
- Never store raw confidential content

## 15. Gemini Performance Optimization

- Use short system prompts
- Use structured JSON output
- Limit token size by trimming transcript
- Cache AI results

## 16. Hackathon Demo Prompt Strategy

For demo:
- Use clean sample transcript
- Pre-test Gemini output
- Avoid ambiguous meetings
- Use predictable input
