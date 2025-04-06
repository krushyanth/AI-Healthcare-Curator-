// Wearable device connection status
let isWearableConnected = false;
let userId = localStorage.getItem('userId');

// WebSocket connection for real-time updates
const ws = new WebSocket('ws://localhost:8080');

ws.onopen = () => {
    console.log('Connected to health monitoring server');
    isWearableConnected = true;
    updateWearableStatus();
};

ws.onclose = () => {
    console.log('Disconnected from health monitoring server');
    isWearableConnected = false;
    updateWearableStatus();
};

// Update wearable connection status indicator
function updateWearableStatus() {
    const statusIndicator = document.querySelector('.status-indicator');
    statusIndicator.className = `status-indicator ${isWearableConnected ? 'connected' : 'disconnected'}`;
    const statusText = document.querySelector('.wearable-status span');
    statusText.textContent = `Wearable Device ${isWearableConnected ? 'Connected' : 'Disconnected'}`;
}

// Fetch health data from backend API
async function fetchHealthData(metric, duration = '24h') {
    try {
        const response = await fetch(`/backend/wearable_api.php?user_id=${userId}&metric=${metric}&duration=${duration}`);
        const data = await response.json();
        return data.status === 'success' ? data.data : [];
    } catch (error) {
        console.error('Error fetching health data:', error);
        return [];
    }
}

// Update vital signs with real data
async function updateVitalSigns() {
    if (!isWearableConnected || !userId) return;

    try {
        const response = await fetch('/backend/wearable_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                health_data: {
                    heart_rate: document.getElementById('heart-rate').textContent,
                    blood_pressure: document.getElementById('blood-pressure').textContent,
                    temperature: document.getElementById('temperature').textContent,
                    blood_oxygen: document.getElementById('oxygen').textContent
                }
            })
        });

        if (!response.ok) throw new Error('Failed to update health data');
    } catch (error) {
        console.error('Error updating vital signs:', error);
    }
}

// Initialize and update charts with real data
async function initializeCharts() {
    const heartRateData = await fetchHealthData('heart_rate', '1h');
    const activityData = await fetchHealthData('steps', '1w');

    // Heart Rate Chart
    const heartCtx = document.getElementById('heartRateChart').getContext('2d');
    new Chart(heartCtx, {
        type: 'line',
        data: {
            labels: heartRateData.map(d => new Date(d.timestamp).toLocaleTimeString()),
            datasets: [{
                label: 'Heart Rate',
                data: heartRateData.map(d => d.heart_rate),
                borderColor: '#007bff',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: activityData.map(d => new Date(d.timestamp).toLocaleDateString()),
            datasets: [{
                label: 'Steps',
                data: activityData.map(d => d.steps),
                backgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Update health insights based on data analysis
async function updateHealthInsights() {
    const heartRateData = await fetchHealthData('heart_rate', '24h');
    const bloodPressureData = await fetchHealthData('blood_pressure', '24h');
    const activityData = await fetchHealthData('steps', '24h');

    const insights = [];
    
    // Analyze heart rate
    const avgHeartRate = heartRateData.reduce((sum, d) => sum + parseInt(d.heart_rate), 0) / heartRateData.length;
    if (avgHeartRate < 60) insights.push('Your average heart rate is below normal. Consider consulting a doctor.');
    else if (avgHeartRate > 100) insights.push('Your average heart rate is elevated. Monitor your stress levels and activity.');
    else insights.push('Your heart rate has been within normal range');

    // Analyze blood pressure
    const lastBP = bloodPressureData[0];
    if (lastBP) {
        const [systolic, diastolic] = lastBP.blood_pressure.split('/');
        if (parseInt(systolic) > 140 || parseInt(diastolic) > 90) {
            insights.push('Your blood pressure is higher than normal. Consider lifestyle changes and consult a doctor.');
        } else {
            insights.push('Blood pressure readings indicate optimal levels');
        }
    }

    // Analyze activity
    const dailySteps = activityData.reduce((sum, d) => sum + parseInt(d.steps), 0);
    const goalSteps = 10000;
    const percentage = Math.round((dailySteps / goalSteps) * 100);
    insights.push(`You've achieved ${percentage}% of your daily activity goal`);

    // Update insights in DOM
    const insightsList = document.querySelector('#health-insights ul');
    insightsList.innerHTML = insights.map(insight => `<li>${insight}</li>`).join('');
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    if (!userId) {
        window.location.href = 'index.html';
        return;
    }

    initializeCharts();
    updateHealthInsights();
    
    // Update vital signs every 3 seconds
    setInterval(updateVitalSigns, 3000);
    
    // Update insights every 5 minutes
    setInterval(updateHealthInsights, 300000);
});