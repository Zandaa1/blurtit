# AI-Powered Blurting Companion

## Overview
The **AI-Powered Blurting Companion** is a capstone project designed to support students in practicing **retrieval-based learning** through the **blurting method**. The system leverages **generative AI models** (e.g., gpt-oss-20b, Gemini, or OpenAI APIs) to evaluate student responses, provide corrective feedback, and deliver personalized insights. This project combines principles from **cognitive psychology** with modern AI to improve **memory retention, engagement, and exam performance**.

## Features
- **User Authentication** – Secure login and registration with role-based access (User/Admin).
- **Blurting Practice Interface** – Students type recall-based responses in real time.
- **AI Feedback System** – Immediate scoring, corrections, and reinforcement using generative AI.
- **Dashboard** – Tracks past sessions, performance history, and improvement trends.
- **Progressive Web App (PWA)** – Can be installed on mobile devices for native-like usage.
- **Database Integration** – MySQL backend to store users, sessions, and feedback securely.

## System Architecture
- **Frontend**: Bootstrap / Tailwind + JavaScript  
- **Backend**: PHP 8+ (with XAMPP for local development)  
- **Database**: MySQL (managed via phpMyAdmin)  
- **AI Integration**: gpt-oss-20b / Gemini API / OpenAI API  
- **Environment**: XAMPP for development and deployment  

## Gemini API configuration
- Set the `GEMINI_API_KEY` environment variable when running PHP. On Windows PowerShell:
	```powershell
	setx GEMINI_API_KEY "your-real-api-key"
	```
	Restart the shell or web server after setting it.
- Alternatively, copy `.env.example` to `.env` at the project root and add your key:
	```
	GEMINI_API_KEY=your-real-api-key
	```
	The application automatically reads this file if the environment variable is missing.
- After pulling vendor updates, double-check that `vendor/gemini-api-php/client/src/Resources/ModelName.php` includes constants for the Gemini models you intend to call. If newer options are missing (for example `public const GEMINI_2_5_FLASH = 'gemini-2.5-flash';` or `public const GEMINI_2_5_PRO = 'gemini-2.5-pro';`), add them to that file so the client can target the correct model identifier.

## Hardware Requirements
- CPU: Intel i5 8th Gen / AMD Ryzen 5 (or higher)  
- RAM: 8GB (16GB recommended)  
- Storage: 256GB SSD  
- Internet: Minimum 10 Mbps for AI API integration  

## Software Requirements
- OS: Windows 10+ (Linux optional for deployment)  
- Tools: PHP, MySQL, XAMPP, JavaScript, Bootstrap/Tailwind  
- Integration: Gemini API Studio / OpenAI API, GitHub for version control  

## Methodology
This project follows the **Software Development Life Cycle (SDLC)**:
1. **Planning** – Define objectives and required technologies.  
2. **Requirement Analysis** – Identify functional needs and stakeholders.  
3. **System Design** – Create UI prototypes and ERD schema.  
4. **Implementation** – Develop UI, backend, and AI integration.  
5. **Testing** – Unit, integration, load, and security testing.  
6. **Deployment** – Deploy via XAMPP or hosting environment.  
7. **Maintenance** – Regular updates, bug fixes, and AI model improvements.  

## Research Basis
The system is grounded in literature emphasizing:  
- **Retrieval Practice** as an effective learning strategy.  
- **Generative AI** in supporting active recall through automated question generation and real-time feedback.  
- **AI Tutoring Systems** for personalized learning and cognitive engagement.  

## Contribution
This study addresses the gap in **spontaneous recall strategies** (like blurting), combining **evidence-based pedagogy** with **AI-driven personalization** to enhance learning outcomes and support student performance.  

## License
This project is for **academic purposes only** as part of a capstone requirement at **Philippine Women’s University**.  
