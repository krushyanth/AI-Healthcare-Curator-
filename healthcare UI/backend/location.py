from flask import Flask, request, jsonify
import geocoder
import googlemaps
from geopy.distance import geodesic
import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

app = Flask(__name__)

# Get Google Maps API key from environment variable
GOOGLE_MAPS_API_KEY = os.getenv('GOOGLE_MAPS_API_KEY')
SEARCH_RADIUS = int(os.getenv('SEARCH_RADIUS', 5000))  # Default 5km radius

def get_user_location():
    try:
        g = geocoder.ip('me')
        if g.latlng:
            return g.latlng
        raise Exception("Could not determine location")
    except Exception as e:
        raise Exception(f"Error getting location: {str(e)}")

def predict_disease(symptoms):
    # This should be replaced with actual ML model prediction
    # Example of basic symptom matching
    symptom_disease_map = {
        'chest_pain': "Cardiac Arrest",
        'bone_fracture': "Fracture",
        'difficulty_breathing': "Pneumonia"
    }
    
    for symptom in symptoms:
        if symptom in symptom_disease_map:
            return symptom_disease_map[symptom]
    return "Unknown"

disease_department_map = {
    "Cardiac Arrest": "cardiology",
    "Fracture": "orthopedic",
    "Pneumonia": "pulmonology",
    "Unknown": "emergency"
}

def find_nearest_hospital(user_location, specialty):
    try:
        if not GOOGLE_MAPS_API_KEY:
            raise Exception("Google Maps API key not configured")
            
        gmaps = googlemaps.Client(key=GOOGLE_MAPS_API_KEY)
        search_query = f"hospital {specialty}"

        places_result = gmaps.places_nearby(
            location=user_location,
            radius=SEARCH_RADIUS,
            keyword=search_query
        )

        if not places_result.get('results'):
            return None

        nearest_hospital = None
        min_distance = float('inf')

        for place in places_result['results']:
            hospital_location = (
                place['geometry']['location']['lat'],
                place['geometry']['location']['lng']
            )
            distance = geodesic(user_location, hospital_location).km
            if distance < min_distance:
                min_distance = distance
                nearest_hospital = {
                    "name": place['name'],
                    "address": place.get('vicinity', 'Address not available'),
                    "distance_km": round(min_distance, 2)
                }

        return nearest_hospital
    except Exception as e:
        raise Exception(f"Error finding hospital: {str(e)}")

@app.route('/find_hospital', methods=['POST'])
def find_hospital():
    try:
        data = request.get_json()
        if not data or 'symptoms' not in data:
            return jsonify({"error": "Symptoms are required"}), 400

        symptoms = data['symptoms']
        user_location = get_user_location()
        disease = predict_disease(symptoms)
        department = disease_department_map.get(disease, "emergency")

        hospital = find_nearest_hospital(user_location, department)
        if hospital:
            return jsonify({
                "disease": disease,
                "hospital_name": hospital['name'],
                "address": hospital['address'],
                "distance_km": hospital['distance_km']
            })
        return jsonify({"error": "No suitable hospital found nearby."}), 404

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)