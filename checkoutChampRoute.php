<?php 

require __DIR__ . '/vendor/autoload.php';

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $variables = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignore comments and trim whitespace
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $variables[trim($key)] = trim($value);
    }

    return $variables;
}
$envVariables = loadEnv(__DIR__ . '/.env');

header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');



$client = new \Google_Client();
$client->setApplicationName('Google Sheets with Primo');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/credentials.json');

$service = new Google_Service_Sheets($client);
$spreadsheetId = $envVariables['SPREAD_SHEET_ID'];

$range = $envVariables['SHEET_NAME']; // Sheet name


function jsonResponse($statusCode, $success, $message) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,

    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }
    
   
        if (
            isset($data['for_me']) && isset($data['for_me']) &&
            isset($data['for_me_answer']) && isset($data['for_me_answer']) &&
            isset($data['for_a_friend']) && isset($data['for_a_friend']) &&
            isset($data['for_a_friend_answer']) && isset($data['for_a_friend_answer']) &&
            isset($data['UUID']) && isset($data['UUID'])
            
            ) {
            $values = [
               [
                 $data['for_me'],
                  $data['for_me_answer'],
                   $data['for_a_friend'],
                    $data['for_a_friend_answer'],
                    date('Y-m-d H:i:s')
                ]
            ];
         
            $body = new Google_Service_Sheets_ValueRange([
            	'values' => $values
            ]);
            $params = [
            	'valueInputOption' => 'RAW'
            ];
            
            $result = $service->spreadsheets_values->append(
            	$spreadsheetId,
            	$range,
            	$body,
            	$params
            );
            
            if($result->updates->updatedRows == 1){
            	echo "Success";
            } else {
            	echo "Fail";
            }
           
        }else{
              jsonResponse(406, false, 'HTTP request does not have right post parameters');
        }
    } catch (Exception $e) {
    // Catch any exceptions and return an error response
        jsonResponse(503, false, 'An unexpected error occurred: ' . $e->getMessage());
    }

} else {
    jsonResponse(405, false, 'Method Not Allowed. Please use POST.');
}

