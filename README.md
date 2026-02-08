# MeetFlow AI Backend

MeetFlow AI is an AI-powered productivity platform that converts meeting conversations into structured, trackable tasks using Gemini 3 API.

## Tech Stack

- **Backend**: Laravel 12.x
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **AI Engine**: Google Gemini API

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure your `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=meetflow
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   GEMINI_API_KEY=your_gemini_api_key
   GEMINI_MODEL=gemini-1.5-pro
   QUEUE_CONNECTION=database
   ```
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Start the queue worker (for async processing):
   ```bash
   php artisan queue:work
   ```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `GET /api/user` - Get authenticated user

### Teams
- `GET /api/teams` - List user's teams
- `POST /api/teams` - Create team
- `GET /api/teams/{id}` - Get team details
- `PUT /api/teams/{id}` - Update team
- `DELETE /api/teams/{id}` - Delete team

### Meetings
- `GET /api/meetings` - List meetings
- `POST /api/meetings` - Create meeting
- `POST /api/meetings/{id}/process` - Process transcript (async)
- `GET /api/meetings/{id}` - Get meeting details
- `DELETE /api/meetings/{id}` - Delete meeting

### Tasks
- `GET /api/tasks` - List tasks (with filters)
- `GET /api/tasks/{id}` - Get task details
- `PUT /api/tasks/{id}` - Update task
- `DELETE /api/tasks/{id}` - Delete task
- `POST /api/tasks/{id}/assign` - Assign task to user
- `POST /api/tasks/{id}/move` - Move task on kanban board

### Kanban
- `GET /api/kanban/boards` - List boards
- `POST /api/kanban/boards` - Create board
- `GET /api/kanban/boards/{id}` - Get board with tasks
- `PUT /api/kanban/boards/{id}/columns` - Update column positions

### Follow-ups & Alerts
- `GET /api/follow-ups` - List follow-up messages
- `POST /api/follow-ups/{id}/send` - Send message
- `GET /api/alerts` - List risk alerts
- `PUT /api/alerts/{id}/resolve` - Resolve alert

### Plus Point Features
- `POST /api/meetings/{id}/summary` - Generate executive summary
- `POST /api/tasks/{id}/whatsapp` - Generate WhatsApp follow-up message
- `GET /api/tasks/productivity?team_id={id}` - Get team productivity score

## Features

- **Meeting Transcript Processing**: Automatically extract tasks from meeting transcripts using Gemini AI
- **Task Management**: Create, assign, and track tasks with priorities and deadlines
- **Kanban Boards**: Organize tasks in customizable Kanban boards
- **Follow-up Messages**: Generate professional follow-up emails
- **Risk Alerts**: Automatic detection of overdue tasks, approaching deadlines, and unassigned high-priority tasks
- **Team Management**: Multi-user teams with role-based access control

### Plus Point Features (Hackathon Optimized)

- **Decision Confidence Score**: AI provides confidence scores (0-100) for each extracted task
- **Executive Summary Generator**: Generate professional meeting summaries with key decisions and insights
- **WhatsApp Follow-up**: Generate short WhatsApp reminder messages (perfect for regional markets)
- **Productivity Score**: Calculate team productivity based on task completion rates
- **Smart Deadline Suggestion**: AI suggests deadlines when not explicitly mentioned

## Usage Example

1. **Register/Login**:
   ```bash
   POST /api/register
   {
     "name": "John Doe",
     "email": "john@example.com",
     "password": "password",
     "password_confirmation": "password"
   }
   ```

2. **Create a Team**:
   ```bash
   POST /api/teams
   {
     "name": "Development Team",
     "description": "Our dev team"
   }
   ```

3. **Create a Meeting**:
   ```bash
   POST /api/meetings
   {
     "team_id": 1,
     "title": "Sprint Planning",
     "transcript": "Meeting transcript here...",
     "participants": ["john@example.com", "jane@example.com"]
   }
   ```

4. **Process Meeting**:
   ```bash
   POST /api/meetings/1/process
   ```

5. **View Tasks**:
   ```bash
   GET /api/tasks?team_id=1
   ```

## Scheduled Jobs

Add to your `app/Console/Kernel.php` or use Laravel's scheduler:

```php
$schedule->job(new \App\Jobs\CheckTaskDeadlinesJob)->daily();
```

## License

MIT
