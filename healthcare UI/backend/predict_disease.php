<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// WHO Disease Dataset (simplified version - should be replaced with complete WHO database)
$diseases_dataset = [
    'Common Cold' => [
        'symptoms' => ['continuous_sneezing', 'chills', 'fatigue', 'cough', 'high_fever'],
        'medicines' => ['Acetaminophen', 'Dextromethorphan', 'Phenylephrine'],
        'severity' => 'mild'
    ],
    'Influenza' => [
        'symptoms' => ['high_fever', 'chills', 'fatigue', 'headache', 'muscle_pain'],
        'medicines' => ['Oseltamivir', 'Zanamivir', 'Ibuprofen'],
        'severity' => 'moderate'
    ],
    'Hypertension' => [
        'symptoms' => ['headache', 'chest_pain', 'shortness_of_breath', 'dizziness'],
        'medicines' => ['Amlodipine', 'Lisinopril', 'Hydrochlorothiazide'],
        'severity' => 'severe'
    ]
];

function calculate_disease_probability($input_symptoms, $disease_symptoms) {
    $matching_symptoms = array_intersect($input_symptoms, $disease_symptoms);
    return count($matching_symptoms) / count($disease_symptoms);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $input_symptoms = $json_data['symptoms'] ?? [];
    
    $predictions = [];
    foreach ($diseases_dataset as $disease => $data) {
        $probability = calculate_disease_probability($input_symptoms, $data['symptoms']);
        if ($probability > 0.5) {
            $predictions[] = [
                'disease' => $disease,
                'probability' => $probability,
                'medicines' => $data['medicines'],
                'severity' => $data['severity']
            ];
        }
    }
    
    usort($predictions, function($a, $b) {
        return $b['probability'] <=> $a['probability'];
    });
    
    echo json_encode([
        'status' => 'success',
        'predictions' => $predictions,
        'disclaimer' => 'This is an AI-assisted prediction. Please consult with a healthcare professional for accurate diagnosis.'
    ]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>