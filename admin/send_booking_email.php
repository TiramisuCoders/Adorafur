<?php
/**
 * Send email notification to customer about booking status update
 * 
 * @param array $booking_data Booking information
 * @param string $new_status New booking status
 * @return bool Success status
 */
function send_status_update_email($booking_data, $new_status) {
    // Resend API key - in production, use environment variables
    $resend_api_key = 're_JEUxyLSt_VFLBbpQwLEZPJyUN4h2NijPY';
    
    // Prepare email content
    $customer_name = $booking_data['c_first_name'] . ' ' . $booking_data['c_last_name'];
    $pet_name = $booking_data['pet_name'];
    $service = $booking_data['service_name'];
    $check_in = $booking_data['formatted_check_in'];
    $check_out = isset($booking_data['formatted_check_out']) ? $booking_data['formatted_check_out'] : '';
    
    // Create subject line based on status
    $subject = "Your Pet Booking Has Been " . $new_status;
    
    // Create email content based on status
    $email_content = get_email_template($new_status, $customer_name, $pet_name, $service, $check_in, $check_out);
    
    // Try to use cURL if available
    if (function_exists('curl_init')) {
        try {
            // Set up cURL request to Resend API
            $ch = curl_init('https://api.resend.com/emails');
            
            $payload = json_encode([
                'from' => 'Adorafur Pet Hotel <notifications@adorafur.com>',
                'to' => [$booking_data['c_email']],
                'subject' => $subject,
                'html' => $email_content
            ]);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $resend_api_key,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            
            $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Log email sending
            $log_message = "Email sent to " . $booking_data['c_email'] . " for booking #" . $booking_data['booking_id'] . " status update to " . $new_status;
            error_log($log_message);
            
            return ($status_code >= 200 && $status_code < 300);
        } catch (Exception $e) {
            error_log("Exception when sending email with cURL: " . $e->getMessage());
            // Fall back to mail() function
        }
    }
    
    // Fallback to PHP's mail function if cURL fails or is not available
    try {
        // Set headers for HTML email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Adorafur Pet Hotel <notifications@adorafur.com>\r\n";
        
        // Send email using PHP's mail function
        $mail_result = mail($booking_data['c_email'], $subject, $email_content, $headers);
        
        if ($mail_result) {
            error_log("Email sent using mail() function to: " . $booking_data['c_email']);
        } else {
            error_log("Failed to send email using mail() function");
        }
        
        return $mail_result;
    } catch (Exception $e) {
        error_log("Exception when sending email with mail(): " . $e->getMessage());
        return false;
    }
}

/**
 * Get email template based on booking status
 */
function get_email_template($status, $customer_name, $pet_name, $service, $check_in, $check_out) {
    // Common header
    $header = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Adorafur Pet Hotel - Booking Update</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 20px; }
            h1 { color: #8B4513; }
            .booking-details { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .booking-details p { margin: 5px 0; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
            .status-confirmed { color: #2e7d32; font-weight: bold; }
            .status-completed { color: #1565c0; font-weight: bold; }
            .status-cancelled { color: #c62828; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Adorafur Pet Hotel</h1>
        </div>
        <p>Hi ' . $customer_name . '!</p>
    ';
    
    // Status-specific content
    $content = '';
    $status_class = 'status-' . strtolower($status);
    
    switch ($status) {
        case 'Confirmed':
            $content = '
            <p>Your pet <strong>' . $pet_name . '</strong>\'s booking has been <span class="' . $status_class . '">confirmed</span>!</p>
            <p>We\'re looking forward to taking care of your pet during their stay with us.</p>
            ';
            break;
            
        case 'Completed':
            $content = '
            <p>Your pet <strong>' . $pet_name . '</strong>\'s booking has been <span class="' . $status_class . '">completed</span>.</p>
            <p>We hope your pet enjoyed their time with us! Thank you for choosing Adorafur Pet Hotel.</p>
            ';
            break;
            
        case 'Cancelled':
            $content = '
            <p>Your pet <strong>' . $pet_name . '</strong>\'s booking has been <span class="' . $status_class . '">cancelled</span>.</p>
            <p>If you did not request this cancellation or have any questions, please contact us immediately.</p>
            ';
            break;
            
        default:
            $content = '
            <p>Your pet <strong>' . $pet_name . '</strong>\'s booking has been updated to <span class="' . $status_class . '">' . $status . '</span>.</p>
            ';
    }
    
    // Booking details
    $details = '
        <div class="booking-details">
            <h3>Booking Details:</h3>
            <p><strong>Pet:</strong> ' . $pet_name . '</p>
            <p><strong>Service:</strong> ' . $service . '</p>
            <p><strong>Check-in Date:</strong> ' . $check_in . '</p>';
    
    if (!empty($check_out)) {
        $details .= '<p><strong>Check-out Date:</strong> ' . $check_out . '</p>';
    }
    
    $details .= '
        </div>
        <p>If you have any questions or need to make changes to your booking, please don\'t hesitate to contact us.</p>
        <p>Thank you for choosing Adorafur Pet Hotel!</p>
    ';
    
    // Footer
    $footer = '
        <div class="footer">
            <p>Adorafur Pet Hotel & Daycare</p>
            <p>© ' . date('Y') . ' Adorafur Pet Hotel. All rights reserved.</p>
        </div>
    </body>
    </html>
    ';
    
    return $header . $content . $details . $footer;
}
?>