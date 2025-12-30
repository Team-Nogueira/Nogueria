<?php
// === TEMPORARY DEBUGGING START ===
// These lines will force PHP to show the full error message in the network tab,
// allowing you to see what is causing the "<br /><b>" output.
error_reporting(E_ALL);
ini_set('display_errors', 1);
// === TEMPORARY DEBUGGING END ===


// Set content type for JSON response
header('Content-Type: application/json');

// Include Composer autoloader
// NOTE: Ensure 'composer install' runs successfully inside the Docker container
require __DIR__ . '/vendor/autoload.php';

// --- Database Configuration ---
$host = 'db';
$db         = 'judo_signups_db';
$user = 'root';
// !!! IMPORTANT: CHANGE THIS TO YOUR ACTUAL DATABASE ROOT PASSWORD !!!
$pass = 'your_db_password';    
$charset = 'utf8mb4';

// --- Google Sheets Configuration ---
// NOTE: Ensure this file is present in the container's working directory
$google_client_file = __DIR__ . '/nogueira-judo-sign-up-466c18816a31.json';
$spreadsheetId = '1a3jVqx1IxJol76G4uJL2nsDjAEuHZl2AX4uwSmW6tns';    
$range = 'Sheet1!A1';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

          $json_data = file_get_contents('php://input');
          $data = json_decode($json_data, true);

          if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Invalid JSON data received."]);
                    exit;
          }
             
          // Use coalesce operator (??) for safe access
          $firstName              = $data['firstName'] ?? '';
          $lastName               = $data['lastName'] ?? '';
          $email                        = $data['email'] ?? '';
          $mobileNumber     = $data['mobileNumber'] ?? '';
          $gender                    = $data['gender'] ?? '';
          $age                             = $data['age'] ?? 0;
          $height                    = $data['height'] ?? 0.0;
          $weight                    = $data['weight'] ?? 0.0;
          $bmiValue               = $data['bmiValue'] ?? 0.0;
          $bmiCategory         = $data['bmiCategory'] ?? '';

          // Data Validation (simple check - adjust as needed)
          if (empty($firstName) || empty($email)) {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Missing required fields (First Name or Email)."]);
                    exit;
          }


          // --- 1. Database Connection and Insertion ---
          $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
          $options = [
                    PDO::ATTR_ERRMODE                              => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES         => false,
          ];

          try {
                    $pdo = new PDO($dsn, $user, $pass, $options);
                       
                    $sql = "INSERT INTO judo_signups (first_name, last_name, email, mobile_number, gender, age, height_cm, weight_kg, bmi_value, bmi_category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                       
                    $stmt = $pdo->prepare($sql);
                       
                    $stmt->execute([$firstName, $lastName, $email, $mobileNumber, $gender, $age, $height, $weight, $bmiValue, $bmiCategory]);
                       
          } catch (\PDOException $e) {
                    // Database connection or insert failed - THIS SHOULD RETURN A 500
                    http_response_code(500);
                    error_log("Database Error: " . $e->getMessage());
                    echo json_encode(["status" => "error", "message" => "A server error occurred while saving your data to the database."]);
                    exit;
          }

          // --- 2. Google Sheets Insertion ---
          try {
                    // Authenticate client
                    $client = new Google_Client();
                    $client->setAuthConfig($google_client_file);
                    $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
                    $client->setAccessType('online');
                    $service = new Google_Service_Sheets($client);

                    // Prepare data for Sheets
                    $values = [
                              [
                                        $firstName, $lastName, $email, $mobileNumber, $gender, $age,    
                                        $height, $weight, $bmiValue, $bmiCategory, date('Y-m-d H:i:s')
                              ]
                    ];
                    $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                    $params = ['valueInputOption' => 'RAW'];
                       
                    // Append data to sheet
                    $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

          } catch (Exception $e) {
                    // Sheets failed, but DB was successful. Log the error and return a soft success message (HTTP 200).
                    error_log("Google Sheets API Error: " . $e->getMessage());
                       
                    // Sending 200 because the primary action (DB save) succeeded.
                    http_response_code(200);    
                    echo json_encode(["status" => "warning", "message" => "✅ Your sign-up was saved to the database, but there was an issue syncing to Google Sheets. Please contact support."]);
                    exit;
          }

          // --- Final Success ---
          http_response_code(200);
          echo json_encode(["status" => "success", "message" => "✅ Thank you! Your sign-up was saved to our database and Google Sheet!"]);

} else {
          // Non-POST request
          http_response_code(405);
          echo json_encode(["status" => "error", "message" => "Error: This script must be submitted via POST."]);
}
// Note: No closing ?> tag to prevent accidental whitespace output.
