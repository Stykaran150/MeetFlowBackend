MeetFlow AI – Product Requirements Document (PRD)

STEP 1 — Create This File First
Name it:
MeetFlow AI – Product Requirements Document (PRD)
________________________________________
STEP 2 — Write This Structure (Fill Exactly Like This)
I will give you ready-to-write template 👇
________________________________________
1. Project Overview
Write:
Project Name:
MeetFlow AI
Tagline:
Convert Meetings into Executed Tasks Automatically
Description:
MeetFlow AI is an AI-powered productivity platform that transforms meeting transcripts or audio recordings into structured tasks and project boards. It automatically extracts decisions, assigns responsibilities, sets deadlines, and creates a Kanban-style task board to help teams execute faster without manual work.
________________________________________
2. Problem Statement
Write:
Today, meetings produce decisions and action items, but most teams fail to track them properly. Manual note-taking, forgotten tasks, unclear ownership, and delayed execution lead to productivity loss. Existing tools focus on recording meetings but do not convert conversations into structured execution workflows.
________________________________________
3. Solution Overview
Write:
MeetFlow AI solves this problem by using Gemini 3’s reasoning capabilities to analyze meeting content and automatically generate actionable tasks. The system creates a project task board, assigns owners, prioritize work, predicts delays, and generates follow-up communication — all from a single meeting upload.
________________________________________
4. Target Users
Write:
Primary Users:
●	Startup teams

●	Project managers

●	Remote teams

●	Corporate departments

●	Government offices

Secondary Users:
●	HR teams

●	Consultants

●	Agencies

________________________________________
5. Core Features (MVP Scope)
Write:
1) Meeting Upload
Users can upload:
●	Meeting transcript (TXT/PDF)

●	Audio recording (optional future upgrade)

________________________________________
2) AI Decision Extraction
Using Gemini 3, the system extracts:
●	Key decisions

●	Action items

●	Task owners

●	Deadlines

●	Priority level

________________________________________
3) Auto Task Board Creation
The extracted tasks are automatically placed into a Kanban board with columns:
●	To Do

●	In Progress

●	Done

________________________________________
4) Task Status Management
Users can:
●	Update task status

●	Add comments

●	Track progress

________________________________________
5) AI Follow-Up Generator
Users can generate:
●	Reminder emails

●	Status update messages

●	Team follow-ups

________________________________________
6. Gemini 3 Integration Details
Write:
MeetFlow AI uses Gemini 3 for:
●	Natural language understanding of meeting transcripts

●	Reasoning-based decision extraction

●	Priority and deadline inference

●	Structured JSON output generation

●	Context-aware follow-up message creation

________________________________________
7. User Flow
Write:
1.	User uploads meeting transcript

2.	System sends content to Gemini 3 API

3.	Gemini returns structured task data

4.	Tasks are saved in database

5.	Task board is automatically generated

6.	User manages and tracks execution

________________________________________
8. Technical Architecture
Write:
Frontend:
●	Vue.js web application

●	Kanban task board UI

Backend:
●	Laravel REST API

●	Gemini 3 API integration

●	Task processing service

Database:
●	MySQL/PostgreSQL

Hosting:
●	Cloud hosting (Render/Vercel)

________________________________________
9. Non-Goals (Important For Scope Control)
Write:
For MVP, MeetFlow AI will NOT include:
●	Live video conferencing

●	Real-time chat

●	Calendar integrations

●	Mobile native apps

________________________________________
10. Hackathon Success Metrics
Write:
Success will be measured by:
●	Accurate task extraction

●	Fast AI response time

●	Clear task visualization

●	Smooth demo experience

●	Strong real-world usability

________________________________________
11. Future Roadmap (Optional)
Write:
Planned future improvements include:
●	Live meeting integration

●	Voice-to-text processing

●	Jira and Slack integration

●	Analytics dashboard

●	Enterprise user management

________________________________________
STEP 3 — What You Do After Writing This
After PRD is done:
✅ You design database
 ✅ You design API routes
 ✅ You design UI screens
 ✅ You start coding confidently
 
Technical Architecture Document (TAD) 
DOCUMENT NAME
👉 MeetFlow AI – Technical Architecture Document
________________________________________
1. System Overview
Write:
MeetFlow AI is a web-based AI productivity system that processes meeting transcripts and converts them into structured project tasks. The system consists of a frontend web application, backend API service, AI processing layer using Gemini 3, and a relational database for persistent storage.
________________________________________
2. High-Level Architecture Diagram (Text)
Write:
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

________________________________________
3. Technology Stack
Write:
Frontend:
●	Vue.js (Vite)

●	Axios for API calls

●	Drag-and-drop Kanban UI

________________________________________
Backend:
●	Laravel 11 REST API

●	Queue worker (for AI processing)

●	File upload handling

________________________________________
AI Layer:
●	Gemini 3 API

●	Multimodal reasoning

●	Structured JSON responses

________________________________________
Database:
●	MySQL or PostgreSQL

________________________________________
Hosting:
●	Backend: Render / DigitalOcean

●	Frontend: Vercel / Netlify

________________________________________
4. Database Schema Design
Write this exactly:
________________________________________
users table
Field	Type
id	bigint (PK)
name	string
email	string
password	string
created_at	timestamp
________________________________________
meetings table
Field	Type
id	bigint (PK)
user_id	bigint (FK)
title	string
raw_content	longtext
status	enum (processing, completed)
created_at	timestamp
________________________________________
tasks table
Field	Type
id	bigint (PK)
meeting_id	bigint (FK)
title	string
description	text
assigned_to	string
priority	enum (low, medium, high)
status	enum (todo, in_progress, done)
deadline	date
created_at	timestamp
________________________________________
ai_logs table (Optional but good)
Field	Type
id	bigint
meeting_id	bigint
request_payload	longtext
response_payload	longtext
created_at	timestamp
________________________________________
5. API Endpoints Design
Write:
________________________________________
Authentication
POST /api/register
POST /api/login

________________________________________
Meeting Upload
POST /api/meetings/upload

Input:
●	meeting_title

●	transcript_text

Output:
●	meeting_id

●	status = processing

________________________________________
Trigger AI Processing
POST /api/meetings/{id}/analyze

This sends meeting text to Gemini.
________________________________________
Fetch Task Board
GET /api/meetings/{id}/tasks

Returns all generated tasks.
________________________________________
Update Task Status
PUT /api/tasks/{id}/status

________________________________________
Generate Follow-Up Message
POST /api/tasks/{id}/followup

________________________________________
6. Gemini 3 Prompt Architecture (IMPORTANT)
Write:
________________________________________
System Prompt:
You are a meeting analysis assistant.
Extract actionable tasks from meeting text.
Return structured JSON.

________________________________________
User Prompt Template:
Analyze this meeting transcript:

{{MEETING_TEXT}}

Return JSON with:

- task_title
- description
- owner
- priority
- deadline

________________________________________
Expected Output Format:
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

________________________________________
7. AI Processing Flow
Write:
1.	Meeting transcript is uploaded

2.	Backend sends content to Gemini 3

3.	Gemini returns structured task data

4.	Backend validates output

5.	Tasks stored in database

6.	Frontend task board updated

________________________________________
8. Task Board Logic (Kanban)
Write:
Columns mapping:
●	todo → status = todo

●	in_progress → status = in_progress

●	done → status = done

Drag & drop updates status via API.
________________________________________
9. Security Considerations
Write:
●	Input validation on uploads

●	API authentication using Sanctum or JWT

●	Rate limiting on Gemini calls

●	No sensitive data storage

________________________________________
10. Performance Strategy
Write:
●	Use queue workers for AI processing

●	Async Gemini calls

●	Cache AI responses

●	Lazy load task board

________________________________________
11. Development Phases
Write:
________________________________________
Phase 1 (Days 1–5)
●	Auth system

●	Meeting upload

●	Database migrations

________________________________________
Phase 2 (Days 6–10)
●	Gemini integration

●	Task extraction logic

●	Store results

________________________________________
Phase 3 (Days 11–15)
●	Kanban UI

●	Task update system

________________________________________
Phase 4 (Days 16–20)
●	Follow-up generator

●	UI polish

________________________________________
Phase 5 (Days 21–22)
●	Demo video

●	Bug fixing

●	Submission

________________________________________ 
Project Structure & API Specification 
DOCUMENT NAME
👉 MeetFlow AI – Project Structure & API Specification
________________________________________
1. Backend Folder Structure (Laravel)
Write:
app/
 ├── Http/
 │    ├── Controllers/
 │    │      ├── AuthController.php
 │    │      ├── MeetingController.php
 │    │      ├── TaskController.php
 │    │      └── AIController.php
 │
 ├── Services/
 │      └── GeminiService.php
 │
 ├── Jobs/
 │      └── ProcessMeetingJob.php
 │
 ├── Models/
 │      ├── Meeting.php
 │      └── Task.php
 │
 └── Core/
       └── GeminiClient.php

________________________________________
2. Controller Responsibilities
Write:
________________________________________
AuthController
Handles:
●	Register

●	Login

●	Token generation

________________________________________
MeetingController
Handles:
●	Upload meeting transcript

●	Fetch meeting info

●	Trigger AI analysis

________________________________________
TaskController
Handles:
●	Fetch tasks

●	Update status

●	Generate follow-up

________________________________________
AIController
Handles:
●	Gemini calls

●	Parsing response

●	Storing results

________________________________________
3. Service Layer Design
Write:
________________________________________
GeminiService.php
Purpose:
●	Format prompts

●	Call Gemini API

●	Handle errors

●	Return structured response

Methods:
analyzeMeeting($text)
generateFollowUp($task)

________________________________________
4. Job Queue Design (Important)
Write:
________________________________________
ProcessMeetingJob.php
Purpose:
●	Run AI processing asynchronously

Flow:
1.	Receives meeting_id

2.	Fetch transcript

3.	Call GeminiService

4.	Save tasks

5.	Update meeting status

This avoids blocking API.
________________________________________
5. API Routes Structure
Write:
________________________________________
Auth Routes
POST /api/register
POST /api/login

________________________________________
Meeting Routes
POST /api/meetings
POST /api/meetings/{id}/analyze
GET /api/meetings/{id}

________________________________________
Task Routes
GET /api/meetings/{id}/tasks
PUT /api/tasks/{id}/status
POST /api/tasks/{id}/followup

________________________________________
6. Request & Response Example
Write:
________________________________________
Upload Meeting
Request:
{
  "title": "Weekly Sprint Meeting",
  "transcript": "Ali will prepare proposal by Friday..."
}

Response:
{
  "meeting_id": 12,
  "status": "processing"
}

________________________________________
Fetch Tasks
Response:
[
  {
    "id": 1,
    "title": "Prepare proposal",
    "owner": "Ali",
    "priority": "High",
    "status": "todo",
    "deadline": "2026-02-10"
  }
]

________________________________________
7. Frontend Folder Structure (Vue)
Write:
src/
 ├── views/
 │      ├── Login.vue
 │      ├── UploadMeeting.vue
 │      └── TaskBoard.vue
 │
 ├── components/
 │      ├── TaskCard.vue
 │      ├── KanbanColumn.vue
 │      └── FollowUpModal.vue
 │
 ├── services/
 │      └── api.js
 │
 └── router/
        └── index.js

________________________________________
8. Frontend Page Responsibilities
Write:
________________________________________
UploadMeeting.vue
Features:
●	Text input for transcript

●	Submit button

●	Processing indicator

________________________________________
TaskBoard.vue
Features:
●	Display Kanban board

●	Drag & drop

●	Status update

________________________________________
FollowUpModal.vue
Features:
●	Show AI generated follow-up

●	Copy button

________________________________________
9. State Flow
Write:
Upload Transcript
     ↓
API Save Meeting
     ↓
Trigger AI Job
     ↓
Tasks Saved
     ↓
Frontend Fetch Tasks
     ↓
Kanban Render

________________________________________
10. Coding Order (IMPORTANT)
Write:
________________________________________
Step 1:
Create database migrations & models
________________________________________
Step 2:
Auth system + API base setup
________________________________________
Step 3:
Meeting upload API
________________________________________
Step 4:
Gemini integration service
________________________________________
Step 5:
Queue job processing
________________________________________
Step 6:
Task APIs
________________________________________
Step 7:
Frontend UI
 
Gemini Prompt Engineering & AI Logic Design 
DOCUMENT NAME
👉 MeetFlow AI – Gemini Prompt Engineering & AI Logic Design
________________________________________
1. AI Design Philosophy
Write:
MeetFlow AI uses Gemini 3 not as a chatbot but as a structured reasoning engine. The AI is instructed to extract actionable information, make priority judgments, infer deadlines, and return clean JSON output that can be directly used by the application.
________________________________________
2. Input Types to Gemini
Write:
MeetFlow AI accepts the following inputs:
●	Meeting transcript text

●	Meeting summary text

●	Structured context metadata (optional)

________________________________________
3. Core Prompt Structure
Write:
Each Gemini request consists of:
1.	System Instruction

2.	Task Definition

3.	Output Format Constraint

4.	Validation Rules

________________________________________
4. System Instruction Prompt
Use this EXACT base prompt:
You are an enterprise meeting analysis assistant.
Your task is to extract actionable tasks and decisions from meeting transcripts.
Be precise, professional, and structured.
Do not invent information.
Return only valid JSON.

________________________________________
5. Task Extraction Prompt Template
Write:
Analyze the following meeting transcript:

{{MEETING_TEXT}}

Extract all actionable tasks and decisions.

For each task return:

- title (short action title)
- description (what needs to be done)
- owner (person responsible)
- priority (Low, Medium, High)
- deadline (ISO format YYYY-MM-DD or null)

Only include tasks that require action.

________________________________________
6. Output Schema Definition
Write:
Output must strictly follow this JSON format:

{
  "tasks": [
    {
      "title": "",
      "description": "",
      "owner": "",
      "priority": "",
      "deadline": ""
    }
  ]
}

________________________________________
7. Priority Inference Rules
Write:
Gemini should infer priority using:
●	Urgency of language (ASAP, urgent, immediately)

●	Business impact keywords

●	Deadline proximity

Rules:
●	Critical deadline or urgent → High

●	Normal work → Medium

●	Optional or future planning → Low

________________________________________
8. Deadline Inference Rules
Write:
If transcript mentions:
●	"by Friday" → Convert to nearest calendar date

●	"next week" → Set 7 days ahead

●	"end of month" → Last day of current month

If no deadline mentioned:
Return null.
________________________________________
9. Error Handling Strategy
Write:
If transcript has:
●	No clear action items → Return empty tasks array

●	Ambiguous owner → Set owner as "Unassigned"

●	Missing data → Do not hallucinate

________________________________________
10. Follow-Up Message Generator Prompt
Write:
________________________________________
Follow-Up Prompt Template:
Generate a professional follow-up message for this task:

Task Title: {{TITLE}}
Owner: {{OWNER}}
Deadline: {{DEADLINE}}
Priority: {{PRIORITY}}

Tone: Professional and polite.
Length: Short email format.

________________________________________
11. Delay Risk Prediction Prompt
Write:
________________________________________
Risk Analysis Prompt:
Analyze this task:

Title: {{TITLE}}
Deadline: {{DEADLINE}}
Priority: {{PRIORITY}}

Predict risk level (Low, Medium, High) and provide short explanation.

________________________________________
12. AI Safety Guidelines
Write:
MeetFlow AI must:
●	Avoid generating sensitive personal data

●	Avoid legal or financial advice

●	Provide neutral and professional outputs

●	Never store raw confidential content

________________________________________
13. Gemini Performance Optimization
Write:
●	Use short system prompts

●	Use structured JSON output

●	Limit token size by trimming transcript

●	Cache AI results

________________________________________
14. Hackathon Demo Prompt Strategy
Write:
For demo:
●	Use clean sample transcript

●	Pre-test Gemini output

●	Avoid ambiguous meetings

●	Use predictable input
 
Submission & Demo Strategy Document 
This final document is what separates average submissions from winners.
________________________________________
📄 MeetFlow AI — Hackathon Submission & Demo Strategy Document
Copy this exactly.
________________________________________
1. Project Positioning (How You Present It)
Write:
MeetFlow AI is an AI-powered decision-to-execution platform that transforms meeting conversations into structured, trackable tasks. It bridges the gap between communication and productivity by automatically generating action items, assigning ownership, predicting delays, and managing execution workflows.
________________________________________
2. One-Line Hook (Judges Attention Line)
Use this:
“MeetFlow AI turns meetings into automatically managed project boards — no manual notes, no forgotten tasks.”
________________________________________
3. Problem → Solution Story (Pitch Flow)
Write:
Problem:
Teams waste hours in meetings but lose productivity due to forgotten action items, unclear ownership, and manual task management.
________________________________________
Solution:
MeetFlow AI analyzes meeting transcripts using Gemini 3 reasoning, extracts actionable decisions, and instantly creates a Kanban task board with ownership, deadlines, and follow-up automation.
________________________________________
Impact:
Organizations save time, reduce execution delays, and improve accountability without changing existing workflows.
________________________________________
4. Gemini 3 Usage Description (Submission Text)
This is the exact 200-word description you can use:
________________________________________
Gemini Integration Description:
MeetFlow AI uses Gemini 3 as a structured reasoning engine to analyze meeting transcripts and extract actionable intelligence. The system leverages Gemini’s natural language understanding to identify decisions, action items, responsible owners, priorities, and deadlines from unstructured meeting conversations. Gemini 3 is also used to infer task urgency, predict execution risk, and generate professional follow-up messages for team communication.
Instead of using Gemini as a chatbot, MeetFlow AI integrates it deeply into the workflow pipeline by converting AI outputs into structured JSON that automatically populates a live Kanban task board. This allows teams to instantly transition from discussion to execution. The low-latency responses of Gemini 3 enable near real-time task generation and visualization, creating a seamless productivity experience. By combining reasoning, structured output generation, and contextual understanding, MeetFlow AI demonstrates how Gemini 3 can power intelligent automation beyond traditional chat interfaces.
________________________________________
5. Demo Video Script (3 Minutes)
Follow this exactly.
________________________________________
🎬 Scene 1 (0–30s)
Show homepage.
Say:
“This is MeetFlow AI. It converts meetings into actionable task boards using Gemini 3.”
________________________________________
🎬 Scene 2 (30–70s)
Upload meeting transcript.
Say:
“I upload a real meeting transcript where decisions were discussed.”
Click Analyze.
________________________________________
🎬 Scene 3 (70–120s)
Show tasks appearing.
Say:
“Gemini automatically extracted tasks, owners, deadlines, and priorities.”
Scroll task board.
________________________________________
🎬 Scene 4 (120–160s)
Move task from To Do → In Progress.
Say:
“Teams can instantly track execution using the Kanban board.”
________________________________________
🎬 Scene 5 (160–180s)
Click Generate Follow-up.
Say:
“MeetFlow AI also generates professional follow-ups to ensure accountability.”
Show output.
Finish:
“MeetFlow AI bridges the gap between meetings and execution.”
________________________________________
6. Demo Transcript Sample (Use This For Testing)
Use this example:
Ali will prepare the project proposal by Friday.
Sara will contact the marketing agency next week.
We need to finalize the budget by the end of this month.

This produces clean AI output.
________________________________________
7. Submission Checklist
Before submitting:
✅ Public demo URL working
 ✅ No login wall
 ✅ Gemini API key secured
 ✅ Demo video under 3 minutes
 ✅ GitHub repository public
 ✅ Clear README file
________________________________________
8. GitHub README Structure
Write:
________________________________________
MeetFlow AI
Description:
AI-powered meeting-to-task automation platform.
Features:
●	Meeting transcript analysis

●	AI task extraction

●	Kanban board

●	Follow-up generator

Tech Stack:
●	Laravel

●	Vue.js

●	Gemini 3 API

Demo:
Link here.
________________________________________
9. Visual Polish Tips (High Impact)
Judges notice UI:
✔ Use clean white background
 ✔ Use status colors (green/yellow/red)
 ✔ Add loading animation during AI processing
 ✔ Show "Powered by Gemini 3" badge
________________________________________
10. Winning Psychology Tips
Judges score higher when they see:
✅ Business usefulness
 ✅ AI solving real problem
 ✅ Easy demo
 ✅ Clear reasoning usage
 ✅ Execution clarity
Avoid:
❌ Over explaining tech
 ❌ Long videos
 ❌ Complex UI
________________________________________
FINAL STEP — Start Building
Now your execution order:
1️⃣ Setup backend project
 2️⃣ Setup frontend
 3️⃣ Implement meeting upload
 4️⃣ Integrate Gemini
 5️⃣ Build Kanban board
 6️⃣ Polish demo
 
BEST PLUS POINT FEATURES 
Here are BEST PLUS POINT FEATURES for MeetFlow AI.
________________________________________
🥇 1) Decision Confidence Score (VERY EASY + WOW)
What It Does:
Gemini assigns confidence to each task:
●	High confidence (clearly stated)

●	Medium (implicit)

●	Low (ambiguous)

________________________________________
Example Output:
Prepare proposal  
Confidence: 92%

________________________________________
Why Judges Like It:
Shows AI reasoning transparency.
________________________________________
Easy Implementation:
Just add to prompt:
Add confidence_score (0-100)

No extra backend logic.
________________________________________
🥈 2) Auto Priority Color Coding (Simple Visual Boost)
What You Do:
Color tasks:
●	🔴 High priority

●	🟡 Medium

●	🟢 Low

________________________________________
Impact:
Makes dashboard professional.
________________________________________
Easy:
Frontend only.
________________________________________
🥉 3) Task Summary Card (One Click Overview)
Feature:
Button:
Generate Executive Summary
Gemini creates:
●	What was decided

●	How many tasks

●	Urgent deadlines

________________________________________
Judges Love:
Management-style reporting.
________________________________________
Easy:
Reuse same API.
________________________________________
🏅 4) Language Auto Detection (Pakistan Advantage)
Feature:
Allow Urdu/English mixed meeting.
Gemini auto-detects language.
________________________________________
Why Important:
Unique regional use case.
________________________________________
Easy:
Gemini handles automatically.
________________________________________
🧠 5) Delay Risk Badge (Already Planned — Keep It)
Add badge:
⚠ High Risk

________________________________________
📅 6) Smart Deadline Suggestion (Easy + Smart)
If no deadline:
Gemini suggests:
●	Based on task complexity

●	Typical duration

Example:
Proposal → 3 days

________________________________________
🔔 7) One Click WhatsApp Follow-Up Message
Instead of email only:
Add:
Generate WhatsApp Reminder
Short message format.
Pakistan users LOVE WhatsApp.
________________________________________
📊 8) Productivity Score (Simple Formula)
Calculate:
●	Completed tasks %

●	Pending %

Show:
Team Productivity: 75%

Easy math.
________________________________________
🔥 BEST 4 FEATURES YOU SHOULD ADD (Hackathon Optimized)
I strongly recommend you add these:
✅ Decision Confidence Score
✅ Priority Color Coding
✅ Executive Summary Generator
✅ WhatsApp Follow-up Generator
These add BIG value with LOW effort.
________________________________________
Bonus Trick (Judge Psychology)
Add badge:
Powered by Gemini 3 Reasoning Engine
And show:
●	Raw transcript

●	Structured AI output

Side by side.
This visually proves AI value.
________________________________________
Implementation Priority Order
After core MVP:
1️⃣ Priority colors
 2️⃣ Confidence score
 3️⃣ Summary generator
 4️⃣ WhatsApp message
