# MeetFlow AI – Technical Architecture Document

## 1. System Overview

MeetFlow AI is a web-based AI productivity system that processes meeting transcripts and converts them into structured project tasks. The system consists of a frontend web application, backend API service, AI processing layer using Gemini 3, and a relational database for persistent storage.

## 2. High-Level Architecture Diagram (Text)

```
User (Browser)
     ↓
Vue.js Frontend
     ↓
Laravel REST API
     ↓
Gemini 3 API
     ↓
AI Task Processor
     ↓
Database (MySQL/Postgres)
```

## 3. Technology Stack

**Frontend:**
- Vue.js (Vite)
- Axios for API calls
- Drag-and-drop Kanban UI

**Backend:**
- Laravel 12 REST API
- Queue worker (for AI processing)
- File upload handling

**AI Layer:**
- Gemini 3 API
- Multimodal reasoning
- Structured JSON responses

**Database:**
- MySQL or PostgreSQL

**Hosting:**
- Backend: Render / DigitalOcean
- Frontend: Vercel / Netlify

## 4. Database Schema Design

### users table
| Field | Type |
|-------|------|
| id | bigint (PK) |
| name | string |
| email | string |
| password | string |
| created_at | timestamp |

### meetings table
| Field | Type |
|-------|------|
| id | bigint (PK) |
| user_id | bigint (FK) |
| title | string |
| raw_content | longtext |
| status | enum (processing, completed) |
| created_at | timestamp |

### tasks table
| Field | Type |
|-------|------|
| id | bigint (PK) |
| meeting_id | bigint (FK) |
| title | string |
| description | text |
| assigned_to | string |
| priority | enum (low, medium, high) |
| status | enum (todo, in_progress, done) |
| deadline | date |
| created_at | timestamp |

### ai_logs table (Optional but good)
| Field | Type |
|-------|------|
| id | bigint |
| meeting_id | bigint |
| request_payload | longtext |
| response_payload | longtext |
| created_at | timestamp |

## 5. API Endpoints Design

### Authentication
- POST /api/register
- POST /api/login

### Meeting Upload
- POST /api/meetings/upload

**Input:**
- meeting_title
- transcript_text

**Output:**
- meeting_id
- status = processing

### Trigger AI Processing
- POST /api/meetings/{id}/analyze

This sends meeting text to Gemini.

### Fetch Task Board
- GET /api/meetings/{id}/tasks

Returns all generated tasks.

### Update Task Status
- PUT /api/tasks/{id}/status

### Generate Follow-Up Message
- POST /api/tasks/{id}/followup

## 6. Gemini 3 Prompt Architecture (IMPORTANT)

### System Prompt:
You are a meeting analysis assistant.
Extract actionable tasks from meeting text.
Return structured JSON.

### User Prompt Template:
Analyze this meeting transcript:

{{MEETING_TEXT}}

Return JSON with:

- task_title
- description
- owner
- priority
- deadline

### Expected Output Format:
```json
{
  "tasks": [
    {
      "title": "Prepare project proposal",
      "description": "Draft initial project plan",
      "owner": "Ahmed",
      "priority": "High",
      "deadline": "2026-02-15"
    }
  ]
}
```

## 7. AI Processing Flow

1. Meeting transcript is uploaded
2. Backend sends content to Gemini 3
3. Gemini returns structured task data
4. Backend validates output
5. Tasks stored in database
6. Frontend task board updated

## 8. Task Board Logic (Kanban)

Columns mapping:
- todo → status = todo
- in_progress → status = in_progress
- done → status = done

Drag & drop updates status via API.

## 9. Security Considerations

- Input validation on uploads
- API authentication using Sanctum or JWT
- Rate limiting on Gemini calls
- No sensitive data storage

## 10. Performance Strategy

- Use queue workers for AI processing
- Async Gemini calls
- Cache AI responses
- Lazy load task board

## 11. Development Phases

### Phase 1 (Days 1–5)
- Auth system
- Meeting upload
- Database migrations

### Phase 2 (Days 6–10)
- Gemini integration
- Task extraction logic
- Store results

### Phase 3 (Days 11–15)
- Kanban UI
- Task update system

### Phase 4 (Days 16–20)
- Follow-up generator
- UI polish

### Phase 5 (Days 21–22)
- Demo video
- Bug fixing
- Submission
