#!/bin/bash

# Heroku deployment script for AI Health Curator MVP

# Install Heroku CLI if not installed
if ! command -v heroku &> /dev/null; then
    curl https://cli-assets.heroku.com/install.sh | sh
fi

# Create Heroku app
heroku create ai-health-curator-mvp

# Add MySQL addon
heroku addons:create jawsdb:kitefin

# Set environment variables
heroku config:set NODE_ENV=production
heroku config:set JWT_SECRET="your-jwt-secret"
heroku config:set API_KEY="your-api-key"

# Deploy application
git add .
git commit -m "Prepare for Heroku deployment"
git push heroku main

# Run database migrations
heroku run "cd backend && php migrate.php"

# Scale web and worker dynos
heroku ps:scale web=1 worker=1

# Print application URL
echo "Your application is deployed at: $(heroku info -s | grep web_url | cut -d= -f2)"