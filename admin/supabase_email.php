<?php
// This file demonstrates how to use Supabase for email functionality
// Note: This is an alternative approach if you prefer to use Supabase instead of Resend

/**
 * Send email using Supabase
 * 
 * @param array $booking_data Booking information
 * @param string $new_status New booking status
 * @return bool Success status
 */
function send_email_via_supabase($booking_data, $new_status) {
    $supabase_url = 'https://ygbwanzobuielhttdzsw.supabase.co';
    $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ';
    
    // Prepare email content
    $customer_name = $booking_data['c_first_name'] . ' ' . $booking_data['c_last_name'];
    $pet_name = $booking_data['pet_name'];
    $service = $booking_data['service_name'];
    $check_in = $booking_data['formatted_check_in'];
    $check_out = isset($booking_data['formatted_check_out']) ? $booking_data['formatted_check_out'] : '';
    
    // Create subject line based on status
    $subject = "Your Pet Booking Has Been " . $new_status;
    
    // Create email content
    $email_content = get_email_template($new_status, $customer_name, $pet_name, $service, $check_in, $check_out);
    
    // Create the email data
    $email_data = [
        'to' => $booking_data['c_email'],
        'subject' => $subject,
        'html_content' => $email_content,
        'booking_id' => $booking_data['booking_id'],
        'status' => $new_status,
        'sent_at' => date('Y-m-d H:i:s')
    ];
    
    // Insert into Supabase email_logs table
    $ch = curl_init($supabase_url . '/rest/v1/email_logs');
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'Prefer: return=minimal'
    ]);
    
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the result
    error_log("Supabase email log result: Status code " . $status_code . ", Response: " . $response);
    
    // You would need to set up a Supabase Edge Function or other service to actually send the email
    // This example just logs the email in Supabase
    
    return ($status_code >= 200 && $status_code < 300);
}
?>
