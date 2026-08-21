<?php
require_once __DIR__ . '/backend/config/app.php';
session_start();

$requestURI = $_SERVER['REQUEST_URI'];

//Get the path of a request only
//removes everything after the question mark in the URL
if (($pos = strpos($requestURI, '?')) !== false){
    $requestURI = substr($requestURI, 0, $pos);
}

//get the name of the root file name, interview-process-management
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseFolder = dirname($scriptName);

//create  aclean url with the project name eg interview-process-management/..
//every link will start with the interview-process-management as the root

$routePath = $requestURI; //clean path

//Strip the base folder so routes always start from the project root
//e.g. "/Interview-Process-Management/login" becomes "/login"
if (strpos($routePath, $baseFolder) === 0 ){
    $routePath = substr($routePath, strlen($baseFolder));
}

//Ensure the path always starts with a single slash and no trailing slash
$routePath = '/' . trim($routePath, '/'); //clean URL

//Routing table

$routes = [
    '/'         =>'frontend/login.php',
    '/login'    =>'frontend/login.php',
    '/register' =>'frontend/register.php',
    '/admin'    =>'frontend/admin-pages/admin-dashboard.php',
    '/ict'      =>'frontend/ict-pages/ict-dashboard.php',
    '/hr'       =>'frontend/hr-pages/hr-dashboard.php',
    '/applicant'=>'frontend/applicant-pages/applicant-dashboard.php',

    '/admin/create-session' => 'frontend/admin-pages/create-session.php',
    '/hr/sessions'          =>'frontend/sessions.php',
    '/reports'              =>'frontend/report.php',
    '/score-card'           =>'frontend/score-card.php',
    '/sessions'             =>'frontend/sessions.php',
    '/applicant/profile'    =>'frontend/applicant-pages/profile.php',
];


//redirects logic
function redirectTo($path){
    header("Location: " . APP_BASE . $path);
    exit();
}

$publicRoutes = ['/', '/login', '/register'];//pages that dont need a login

if(!in_array($routePath, $publicRoutes)){
    //when one wants to get into a restricted page
    if(!isset($_SESSION['user_id'])){
        redirectTo('/login?error=Please+log+in+to+access+this+page');

    }

    $role = $_SESSION['user_role'] ?? '';

    if(strpos($routePath, '/admin') === 0 && $role !== 'admin') {
        redirectTo('/login?error=Access+denied.+Admin+role+required.'); //Redirect back to login if not an admin role
    }

   if(strpos($routePath, '/hr') === 0 && $role !== 'hr') {
        redirectTo('/login?error=Access+denied.+HR+role+required.'); //Redirect back to login if not an hr role
    }

    if(strpos($routePath, '/applicant') === 0 && $role !== 'applicant') {
        redirectTo('/login?error=Access+denied.+applicant+role+required.'); //Redirect back to login if not an applicant role
    }

    if(strpos($routePath, '/ict') === 0 && $role !== 'ict') {
        redirectTo('/login?error=Access+denied.+ICT+role+required.'); //Redirect back to login if not an ICT role
    }
}

//display the page required
if (array_key_exists($routePath, $routes)) {
    // Check if physical file exists before loading
    $targetFile = $routes[$routePath];//looks for the routepath in the $routes table
    if (file_exists($targetFile)) {
        require_once $targetFile; //gets the frontend file eg login.php and loads it in the screen
    } else {
        //errors are loaded if the file was not found or the url was wrong
        http_response_code(500);
        echo "<h1>500 Internal Server Error</h1>";
        echo "<p>Route mapped successfully, but physical file not found: <code>$targetFile</code></p>";
    }
} else {
    // Handle 404 - Page Not Found
    http_response_code(404);
    echo "<h1>404 Page Not Found</h1>";
    echo "<p>The requested route <code>" . htmlspecialchars($routePath) . "</code> does not exist.</p>";
}


?>