# MeetFlow AI – Product Requirements Document (PRD)

## 1. Project Overview

**Project Name:**  
MeetFlow AI

**Tagline:**  
Convert Meetings into Executed Tasks Automatically

**Description:**  
MeetFlow AI is an AI-powered productivity platform that transforms meeting transcripts or audio recordings into structured tasks and project boards. It automatically extracts decisions, assigns responsibilities, sets deadlines, and creates a Kanban-style task board to help teams execute faster without manual work.

## 2. Problem Statement

Today, meetings produce decisions and action items, but most teams fail to track them properly. Manual note-taking, forgotten tasks, unclear ownership, and delayed execution lead to productivity loss. Existing tools focus on recording meetings but do not convert conversations into structured execution workflows.

## 3. Solution Overview

MeetFlow AI solves this problem by using Gemini 3's reasoning capabilities to analyze meeting content and automatically generate actionable tasks. The system creates a project task board, assigns owners, prioritizes work, predicts delays, and generates follow-up communication — all from a single meeting upload.

## 4. Target Users

**Primary Users:**
- Startup teams
- Project managers
- Remote teams
- Corporate departments
- Government offices

**Secondary Users:**
- HR teams
- Consultants
- Agencies

## 5. Core Features (MVP Scope)

### 1) Meeting Upload
Users can upload:
- Meeting transcript (TXT/PDF)
- Audio recording (optional future upgrade)

### 2) AI Decision Extraction
Using Gemini 3, the system extracts:
- Key decisions
- Action items
- Task owners
- Deadlines
- Priority level

### 3) Auto Task Board Creation
The extracted tasks are automatically placed into a Kanban board with columns:
- To Do
- In Progress
- Done

### 4) Task Status Management
Users can:
- Update task status
- Add comments
- Track progress

### 5) AI Follow-Up Generator
Users can generate:
- Reminder emails
- Status update messages
- Team follow-ups

## 6. Gemini 3 Integration Details

MeetFlow AI uses Gemini 3 for:
- Natural language understanding of meeting transcripts
- Reasoning-based decision extraction
- Priority and deadline inference
- Structured JSON output generation
- Context-aware follow-up message creation

## 7. User Flow

1. User uploads meeting transcript
2. System sends content to Gemini 3 API
3. Gemini returns structured task data
4. Tasks are saved in database
5. Task board is automatically generated
6. User manages and tracks execution

## 8. Technical Architecture

**Frontend:**
- Vue.js web application
- Kanban task board UI

**Backend:**
- Laravel REST API
- Gemini 3 API integration
- Task processing service

**Database:**
- MySQL/PostgreSQL

**Hosting:**
- Cloud hosting (Render/Vercel)

## 9. Non-Goals (Important For Scope Control)

For MVP, MeetFlow AI will NOT include:
- Live video conferencing
- Real-time chat
- Calendar integrations
- Mobile native apps

## 10. Hackathon Success Metrics

Success will be measured by:
- Accurate task extraction
- Fast AI response time
- Clear task visualization
- Smooth demo experience
- Strong real-world usability

## 11. Future Roadmap (Optional)

Planned future improvements include:
- Live meeting integration
- Voice-to-text processing
- Jira and Slack integration
- Analytics dashboard
- Enterprise user management
