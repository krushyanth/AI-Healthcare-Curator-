# AI Health Curator MVP

## Overview
AI Health Curator is a comprehensive healthcare monitoring system that integrates wearable devices, real-time health tracking, and AI-powered disease prediction.

## Features
- Real-time health monitoring via WebSocket
- Wearable device integration
- Disease prediction using symptoms analysis
- Hospital location services
- User authentication system

## Prerequisites
1. XAMPP (Apache, MySQL, PHP)
2. Node.js and npm
3. Composer for PHP dependencies
4. Web browser with WebSocket support

## Installation

1. Clone the repository to your XAMPP htdocs directory

2. Set up the database:
   ```sql
   mysql -u root -p < backend/schema.sql
   ```

3. Install PHP dependencies:
   ```bash
   cd backend
   php composer.phar install
   ```

4. Configure the database connection:
   - Update `backend/connection.php` with your database credentials

5. Start the WebSocket server:
   ```bash
   cd backend
   php websocket_server.php
   ```

6. Access the application:
   - Frontend: http://localhost/healthcare-ui/frontend
   - WebSocket server: ws://localhost:8080

## API Endpoints

### Wearable API
- POST `/backend/wearable_api.php`
  - Store health metrics data
- GET `/backend/wearable_api.php?user_id={id}&metric={metric}&duration={duration}`
  - Retrieve health metrics

### Disease Prediction
- POST `/backend/predict_disease.php`
  - Get disease prediction based on symptoms

### Hospital Location
- GET `/backend/location.py`
  - Find nearby hospitals

## Security Considerations
- Implement HTTPS for production
- Secure WebSocket connections (WSS)
- Sanitize all user inputs
- Use prepared statements for database queries
- Store sensitive data in environment variables

## Production Deployment
1. Configure Apache virtual host
2. Set up SSL certificates
3. Configure database with proper user permissions
4. Set up process manager for WebSocket server
5. Enable error logging
6. Implement rate limiting

## Support
For technical support or feature requests, please open an issue in the repository.