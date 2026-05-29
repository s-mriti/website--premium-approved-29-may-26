<?php
// save_registration.php
// Place this file in the ROOT of your Hostinger website (same folder as index.php)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================================
//  ✅ YOUR DETAILS — edit these two lines only
// ============================================================
$YOUR_EMAIL   = 'your@email.com';       // Your email — you get a copy of every registration
$YOUR_NAME    = 'IndoGulf Yoga & Wellness';
$FROM_EMAIL   = 'noreply@yourdomain.com'; // Must match your Hostinger domain
// ============================================================

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Only POST allowed']);
    exit();
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

// Sanitise helper
function clean($val) {
    return isset($val) ? htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8') : '';
}

// Grab all fields
$firstName   = clean($data['firstName']);
$lastName    = clean($data['lastName']);
$fullName    = $firstName . ' ' . $lastName;
$phone       = clean($data['phone']);
$email       = clean($data['email']);
$gender      = clean($data['gender']);
$nationality = clean($data['nationality']);
$area        = clean($data['area']);
$dob         = clean($data['dob']);
$services    = clean($data['services']);
$preferences = clean($data['preferences']);
$classType   = clean($data['classType']);
$timeSlot    = clean($data['timeSlot']);
$notes       = clean($data['notes']);
$cpr         = clean($data['cpr']);
$timestamp   = date('d M Y, h:i A');

// ============================================================
//  SAVE TO CSV
// ============================================================
$csvFile = __DIR__ . '/registrations/indogulf_registrations.csv';
$csvDir  = __DIR__ . '/registrations';

if (!file_exists($csvDir)) {
    mkdir($csvDir, 0755, true);
}

$htaccess = $csvDir . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Deny from all\n");
}

$isNew = !file_exists($csvFile);
$fp    = fopen($csvFile, 'a');

if ($isNew) {
    fputcsv($fp, ['Timestamp','First Name','Last Name','Phone','Email','Gender',
                  'Nationality','Area','Date of Birth','CPR / ID','Services','Preferences',
                  'Class Type','Time Slot','Notes']);
}

fputcsv($fp, [$timestamp, $firstName, $lastName, $phone, $email, $gender,
              $nationality, $area, $dob, $cpr, $services, $preferences,
              $classType, $timeSlot, $notes]);

fclose($fp);

// ============================================================
//  EMAIL HELPERS
// ============================================================
function row($label, $value) {
    if (!$value || $value === 'None') return '';
    return "
        <tr>
          <td style='padding:10px 16px;font-family:Georgia,serif;font-size:13px;
                     color:#888;font-style:italic;white-space:nowrap;
                     border-bottom:1px solid #2a1e30;width:38%;'>{$label}</td>
          <td style='padding:10px 16px;font-family:Arial,sans-serif;font-size:13px;
                     color:#f0f0f0;border-bottom:1px solid #2a1e30;'>{$value}</td>
        </tr>";
}

// ============================================================
//  CLIENT CONFIRMATION EMAIL  (sent to the person registering)
// ============================================================
$clientSubject = "Your IndoGulf Registration — {$fullName}";

$clientBody = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#0f0a14;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#0f0a14;padding:40px 20px;'>
    <tr><td align='center'>
      <table width='580' cellpadding='0' cellspacing='0' style='max-width:580px;width:100%;'>

        <!-- Header -->
        <tr>
          <td style='background:linear-gradient(135deg,#1A1016,#251828);
                     border-top:3px solid #D93A2B;
                     padding:36px 40px 28px;text-align:center;'>
            <p style='margin:0 0 6px;font-family:Georgia,serif;font-size:11px;
                      letter-spacing:4px;text-transform:uppercase;color:#F05A4A;'>
              INDO<span style='color:#aaa;'>GULF</span>
            </p>
            <h1 style='margin:0 0 6px;font-family:Georgia,serif;font-size:26px;
                       font-weight:400;color:#ffffff;letter-spacing:1px;'>
              Registration Confirmed
            </h1>
            <p style='margin:0;font-family:Georgia,serif;font-size:14px;
                      font-style:italic;color:rgba(255,255,255,0.45);'>
              Welcome to your wellness journey
            </p>
            <div style='width:40px;height:1px;background:#D93A2B;margin:16px auto 0;'></div>
          </td>
        </tr>

        <!-- Greeting -->
        <tr>
          <td style='background:#1a1020;padding:28px 40px 20px;'>
            <p style='margin:0 0 12px;font-size:15px;color:#e0e0e0;line-height:1.7;'>
              Dear <strong style='color:#ffffff;'>{$firstName}</strong>,
            </p>
            <p style='margin:0;font-size:14px;color:rgba(255,255,255,0.55);line-height:1.8;'>
              Thank you for registering with IndoGulf Yoga &amp; Wellness. We have received
              your details and our team will be in touch shortly to confirm your batch and
              schedule. Here is a copy of your registration for your records.
            </p>
          </td>
        </tr>

        <!-- Registration Details Table -->
        <tr>
          <td style='background:#1a1020;padding:4px 40px 24px;'>
            <table width='100%' cellpadding='0' cellspacing='0'
                   style='border:1px solid #2a1e30;border-radius:4px;overflow:hidden;'>
              <tr>
                <td colspan='2' style='background:#251828;padding:12px 16px;
                                       font-family:Georgia,serif;font-size:11px;
                                       letter-spacing:3px;text-transform:uppercase;
                                       color:#F05A4A;'>
                  YOUR REGISTRATION DETAILS
                </td>
              </tr>
              " . row('Full Name', $fullName)
                . row('CPR / ID Number', $cpr)
                . row('Phone / WhatsApp', $phone)
                . row('Email', $email)
                . row('Gender', $gender)
                . row('Nationality', $nationality)
                . row('Area', $area)
                . row('Date of Birth', $dob)
                . row('Services', $services)
                . row('Preferences', $preferences)
                . row('Class Type', $classType)
                . row('Preferred Time', $timeSlot)
                . row('Notes / Goals', $notes) . "
              <tr>
                <td style='padding:10px 16px;font-family:Georgia,serif;font-size:13px;
                           color:#888;font-style:italic;'>Registered On</td>
                <td style='padding:10px 16px;font-family:Arial,sans-serif;font-size:13px;
                           color:#f0f0f0;'>{$timestamp}</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- WhatsApp CTA -->
        <tr>
          <td style='background:#1a1020;padding:4px 40px 32px;text-align:center;'>
            <p style='margin:0 0 18px;font-size:13px;color:rgba(255,255,255,0.45);'>
              For the fastest confirmation, reach us directly on WhatsApp
            </p>
            <a href='https://wa.me/97334375102'
               style='display:inline-block;background:#25D366;color:#ffffff;
                      text-decoration:none;padding:13px 32px;
                      font-family:Arial,sans-serif;font-size:12px;
                      letter-spacing:2px;text-transform:uppercase;font-weight:600;'>
              💬 &nbsp;Chat on WhatsApp
            </a>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style='background:#120d18;border-top:1px solid #2a1e30;
                     padding:20px 40px;text-align:center;'>
            <p style='margin:0 0 4px;font-family:Georgia,serif;font-size:11px;
                      letter-spacing:3px;text-transform:uppercase;color:#F05A4A;'>
              INDOGULF YOGA &amp; WELLNESS
            </p>
            <p style='margin:0;font-size:11px;color:rgba(255,255,255,0.25);'>
              Bahrain &nbsp;&middot;&nbsp; +973 3437 5102
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";

// ============================================================
//  ADMIN NOTIFICATION EMAIL  (sent to you)
// ============================================================
$adminSubject = "New Registration: {$fullName} — {$services}";

$adminBody = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#0f0a14;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#0f0a14;padding:40px 20px;'>
    <tr><td align='center'>
      <table width='580' cellpadding='0' cellspacing='0' style='max-width:580px;width:100%;'>

        <tr>
          <td style='background:#251828;border-top:3px solid #D93A2B;
                     padding:24px 40px;text-align:center;'>
            <p style='margin:0 0 4px;font-family:Georgia,serif;font-size:11px;
                      letter-spacing:4px;text-transform:uppercase;color:#F05A4A;'>
              NEW REGISTRATION
            </p>
            <h1 style='margin:0;font-family:Georgia,serif;font-size:22px;
                       font-weight:400;color:#ffffff;'>{$fullName}</h1>
            <p style='margin:6px 0 0;font-size:12px;color:rgba(255,255,255,0.4);'>{$timestamp}</p>
          </td>
        </tr>

        <tr>
          <td style='background:#1a1020;padding:24px 40px;'>
            <table width='100%' cellpadding='0' cellspacing='0'
                   style='border:1px solid #2a1e30;'>
              " . row('Phone / WhatsApp', $phone)
                . row('CPR / ID Number', $cpr)
                . row('Email', $email)
                . row('Gender', $gender)
                . row('Nationality', $nationality)
                . row('Area', $area)
                . row('Services', $services)
                . row('Preferences', $preferences)
                . row('Class Type', $classType)
                . row('Preferred Time', $timeSlot)
                . row('Notes', $notes) . "
            </table>
          </td>
        </tr>

        <tr>
          <td style='background:#120d18;border-top:1px solid #2a1e30;
                     padding:16px 40px;text-align:center;'>
            <p style='margin:0;font-size:11px;color:rgba(255,255,255,0.25);'>
              IndoGulf Admin Notification &nbsp;&middot;&nbsp; {$timestamp}
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";

// ============================================================
//  SEND EMAILS
// ============================================================
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: {$YOUR_NAME} <{$FROM_EMAIL}>\r\n";
$headers .= "Reply-To: {$YOUR_EMAIL}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send to client
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    mail($email, $clientSubject, $clientBody, $headers);
}

// Send copy to admin
mail($YOUR_EMAIL, $adminSubject, $adminBody, $headers);

echo json_encode(['status' => 'success', 'message' => 'Registration saved and emails sent']);
?>
